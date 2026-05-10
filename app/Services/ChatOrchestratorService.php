<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\HostedPage;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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

        if ($resolved['channel'] !== 'hosted_page' || empty($resolved['hosted_page']) || ! is_array($resolved['hosted_page'])) {
            return ['success' => false, 'error' => 'Invalid request'];
        }

        $hostedPageData = $resolved['hosted_page'];
        $hostedPage = HostedPage::query()
            ->where('id', $hostedPageData['id'] ?? 0)
            ->where('status', 'active')
            ->first(['id', 'client_id', 'slug']);
        if (! $hostedPage) {
            return ['success' => false, 'error' => 'Invalid request'];
        }

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
            'session_token' => $this->createSessionToken($session, $payload['visitor_fingerprint'] ?? null),
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
        if (! $this->verifySessionToken($payload['session_token'] ?? null, $session, $payload['visitor_fingerprint'] ?? null)) {
            return ['success' => false, 'error' => 'Unauthorized session'];
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
        if (! $this->verifySessionToken($payload['session_token'] ?? null, $session, $payload['visitor_fingerprint'] ?? null)) {
            return ['success' => false, 'error' => 'Unauthorized session'];
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

    private function createSessionToken(ChatSession $session, ?string $visitorFingerprint): string
    {
        $secret = (string) config('app.key');
        $payload = [
            'sid' => $session->id,
            'cid' => $session->client_id,
            'fp' => sha1((string) ($visitorFingerprint ?? $session->visitor_fingerprint ?? '')),
            'exp' => now()->addHours(24)->timestamp,
        ];

        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $payloadEncoded, $secret);

        return $payloadEncoded.'.'.$signature;
    }

    private function verifySessionToken(?string $token, ChatSession $session, ?string $visitorFingerprint): bool
    {
        if (! $token || ! str_contains($token, '.')) {
            return false;
        }

        [$payloadEncoded, $signature] = explode('.', $token, 2);
        $expected = hash_hmac('sha256', $payloadEncoded, (string) config('app.key'));
        if (! hash_equals($expected, $signature)) {
            return false;
        }

        $json = base64_decode(strtr($payloadEncoded, '-_', '+/'));
        $payload = json_decode($json ?: '', true);
        if (! is_array($payload)) {
            return false;
        }

        $fingerprintHash = sha1((string) ($visitorFingerprint ?? $session->visitor_fingerprint ?? ''));
        $isValid = ($payload['sid'] ?? null) === $session->id
            && (int) ($payload['cid'] ?? 0) === (int) $session->client_id
            && ($payload['fp'] ?? null) === $fingerprintHash
            && (int) ($payload['exp'] ?? 0) >= now()->timestamp;

        if (! $isValid) {
            Log::warning('Hosted session token mismatch', ['session_id' => $session->id]);
        }

        return $isValid;
    }
}
