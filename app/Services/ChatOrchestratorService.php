<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class ChatOrchestratorService
{
    public function __construct(
        private readonly ChatService $chatService,
        private readonly ChannelResolver $channelResolver,
    ) {}

    public function initSession(array $payload, string $ip): array
    {
        $resolved = $this->channelResolver->resolve($payload);

        if ($resolved['channel'] !== 'hosted_page' || empty($resolved['hosted_page'])) {
            return ['success' => false, 'error' => 'Invalid request'];
        }

        $hostedPage = $resolved['hosted_page'];

        $session = ChatSession::create([
            'client_id' => $hostedPage->client_id,
            'hosted_page_id' => $hostedPage->id,
            'channel' => 'hosted_page',
            'channel_ref' => $hostedPage->slug,
            'visitor_fingerprint' => $payload['visitor_fingerprint'] ?? sha1($ip.($payload['user_agent'] ?? '')),
            'lead_status' => 'none',
            'meta' => [
                'source_url' => $payload['source_url'] ?? null,
            ],
        ]);

        return [
            'success' => true,
            'session_id' => $session->id,
            'client_id' => $hostedPage->client_id,
            'channel' => 'hosted_page',
        ];
    }

    public function processMessage(array $payload, string $ip, ?string $userAgent = null): array
    {
        $session = ChatSession::where('id', $payload['session_id'] ?? '')
            ->where('channel', 'hosted_page')
            ->first();

        if (! $session) {
            return ['success' => false, 'error' => 'Invalid session'];
        }

        $rateKey = 'hosted_chat:'.$session->client_id.':'.$ip;
        if (RateLimiter::tooManyAttempts($rateKey, 30)) {
            return ['success' => false, 'error' => 'Rate limit exceeded'];
        }
        RateLimiter::hit($rateKey, 3600);

        $client = User::where('id', $session->client_id)->where('chatbot_enabled', true)->first();
        if (! $client || ! $client->isActive()) {
            return ['success' => false, 'error' => 'Chat unavailable'];
        }

        $result = $this->chatService->processChat(
            $client,
            $payload['message'],
            $session->conversation_id,
            $payload['visitor_id'] ?? $session->visitor_fingerprint,
            $payload['source_url'] ?? null,
            $payload['visitor_name'] ?? null,
            $payload['visitor_email'] ?? null,
            $payload['visitor_phone'] ?? null,
            $ip,
            $userAgent
        );

        if (! ($result['success'] ?? false)) {
            return $result;
        }

        $session->conversation_id = $result['conversation_id'];
        $session->message_count += 1;
        $session->last_message_at = now();
        $session->save();

        $leadTrigger = $this->detectLeadTrigger($payload['message'], $session->message_count);
        if ($leadTrigger) {
            $session->lead_status = 'candidate';
            $session->save();
        }

        return [
            'success' => true,
            'reply' => $result['reply'],
            'conversation_id' => $result['conversation_id'],
            'lead_capture_suggested' => (bool) $leadTrigger,
            'lead_trigger' => $leadTrigger,
        ];
    }

    public function captureLead(array $payload): array
    {
        $session = ChatSession::where('id', $payload['session_id'] ?? '')->first();
        if (! $session) {
            return ['success' => false, 'error' => 'Invalid session'];
        }

        Lead::create([
            'session_id' => $session->id,
            'client_id' => $session->client_id,
            'channel' => $session->channel,
            'lead_data' => [
                'name' => $payload['name'] ?? null,
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ],
            'conversion_trigger' => $payload['trigger'] ?? 'manual',
        ]);

        $session->lead_status = 'captured';
        $session->save();

        return ['success' => true];
    }

    private function detectLeadTrigger(string $message, int $depth): ?string
    {
        $text = strtolower($message);
        if (str_contains($text, 'price') || str_contains($text, 'cost') || str_contains($text, 'pricing')) {
            return 'pricing_intent';
        }
        if (str_contains($text, 'book') || str_contains($text, 'reservation')) {
            return 'booking_intent';
        }
        if (str_contains($text, 'contact') || str_contains($text, 'call me')) {
            return 'contact_intent';
        }
        if ($depth >= 6) {
            return 'conversation_depth';
        }

        return null;
    }
}
