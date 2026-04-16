<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WidgetSessionToken;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private ChatService $chatService;

    private const BLOCKED_PATTERNS = [
        '/ignore\s+previous\s+instructions/i',
        '/ignore\s+all\s+previous/i',
        '/disregard\s+your\s+instructions/i',
        '/system\s+prompt/i',
        '/you\s+are\s+now\s+/i',
        '/act\s+as\s+if/i',
        '/pretend\s+you\s+are/i',
        '/new\s+system:\s*/i',
        '/\\[system\\]/i',
    ];

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function chat(Request $request): JsonResponse
    {
        if (! $request->secure() && ! app()->environment('local')) {
            return response()->json([
                'success' => false,
                'error' => 'HTTPS required',
            ], 403);
        }

        $request->validate([
            'site_id' => 'required|string',
            'message' => 'required|string|max:1000',
            'visitor_id' => 'nullable|string|max:64',
            'conversation_id' => 'nullable|integer',
            'source_url' => 'nullable|string|max:500',
        ]);

        $sessionToken = $request->header('X-Session-Token');

        $session = null;
        $client = null;

        if ($sessionToken) {
            $session = WidgetSessionToken::where('token', $sessionToken)
                ->where('expires_at', '>', now())
                ->first();
        }

        if ($session) {
            $client = $session->user;
            $session->touch();
        }

        if (! $client) {
            $client = User::where('site_id', $request->site_id)
                ->orWhere('api_token', $request->site_id)
                ->first();
        }

        if (! $client || ! $client->chatbot_enabled) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid site',
            ], 401);
        }

        if (! $client->isActive()) {
            return response()->json([
                'success' => false,
                'error' => 'Account suspended',
            ], 403);
        }

        $cleanedMessage = $this->sanitizeInput($request->message);

        $result = $this->chatService->processChat(
            $client,
            $cleanedMessage,
            $request->conversation_id,
            $request->visitor_id,
            $this->sanitizeUrl($request->source_url)
        );

        if (! $result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'reply' => $result['reply'],
            'conversation_id' => $result['conversation_id'],
        ]);
    }

    private function sanitizeInput(string $input): string
    {
        $cleaned = strip_tags($input);

        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $cleaned);
        $cleaned = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $cleaned);
        $cleaned = preg_replace('/javascript:/i', '', $cleaned);
        $cleaned = preg_replace('/on\w+\s*=/i', '', $cleaned);

        foreach (self::BLOCKED_PATTERNS as $pattern) {
            $cleaned = preg_replace($pattern, '[filtered]', $cleaned);
        }

        return trim($cleaned);
    }

    private function sanitizeUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parsed = parse_url($url);

        if (! isset($parsed['host']) || ! in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return null;
        }

        return $parsed['scheme'].'://'.$parsed['host'].($parsed['port'] ? ':'.$parsed['port'] : '');
    }
}
