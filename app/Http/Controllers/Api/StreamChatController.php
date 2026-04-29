<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\TokenUsageLog;
use App\Models\User;
use App\Models\WidgetSessionToken;
use App\Services\ChatButtonParser;
use App\Services\ChatService;
use App\Services\GroqService;
use Illuminate\Http\Request;

class StreamChatController extends Controller
{
    private GroqService $grokService;

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

    public function __construct(GroqService $grokService, ChatService $chatService)
    {
        $this->grokService = $grokService;
        $this->chatService = $chatService;
    }

    public function stream(Request $request)
    {
        if (! $request->secure() && ! app()->environment('local')) {
            return response()->json(['error' => 'HTTPS required'], 403);
        }

        $request->validate([
            'site_id'         => 'required|string',
            'message'         => 'required|string|max:1000',
            'visitor_id'      => 'nullable|string|max:64',
            'conversation_id' => 'nullable|integer',
            'source_url'      => 'nullable|string|max:500',
            'visitor_name'    => 'nullable|string|max:100',
            'visitor_email'   => 'nullable|email|max:191',
            'visitor_phone'   => 'nullable|string|max:30',
        ]);

        $sessionToken = $request->header('X-Session-Token');
        $client       = null;

        if ($sessionToken) {
            $session = WidgetSessionToken::where('token', $sessionToken)
                ->where('expires_at', '>', now())
                ->first();
            if ($session) {
                $client = $session->user;
                $session->touchLastUsed();
            }
        }

        if (! $client) {
            $client = User::where('site_id', $request->site_id)
                ->orWhere('api_token', $request->site_id)
                ->first();
        }

        if (! $client || ! $client->chatbot_enabled) {
            return response()->json(['error' => 'Invalid site'], 401);
        }

        if (! $client->isActive()) {
            return response()->json(['error' => 'Account suspended'], 403);
        }

        $cleanedMessage = $this->sanitizeInput($request->message);
        $conversation   = $this->chatService->getOrCreateConversation(
            $client,
            $request->conversation_id,
            $request->visitor_id,
            $this->sanitizeUrl($request->source_url),
            $request->visitor_name,
            $request->visitor_email,
            $request->visitor_phone
        );
        $this->chatService->upsertVisitor(
            $client,
            $request->visitor_id,
            $request->visitor_name,
            $request->visitor_email,
            $request->visitor_phone,
            $request->ip(),
            $request->userAgent(),
            $this->sanitizeUrl($request->source_url)
        );

        // Fetch history and build messages BEFORE saving visitor message to avoid duplication
        $messages = $this->chatService->buildTokenBudgetedMessages($client, $conversation, $cleanedMessage);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'visitor',
            'message'         => $cleanedMessage,
            'tokens_used'     => 0,
            'created_at'      => now(),
        ]);

        $startTime    = microtime(true);
        $fullResponse = '';
        $grokService  = $this->grokService;
        $chatService  = $this->chatService;

        return response()->stream(function () use (
            $messages, $client, $conversation, $startTime,
            $grokService, $chatService, &$fullResponse
        ) {
            $grokService->streamChat(
                $messages,
                function ($chunk) use (&$fullResponse) {
                    $fullResponse .= $chunk;
                    echo 'data: '.json_encode(['type' => 'chunk', 'content' => $chunk])."\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                },
                function (array $usage) use (&$fullResponse, $client, $conversation, $startTime, $grokService, $chatService) {
                    $endTime   = microtime(true);
                    $tokensIn  = $usage['input_tokens']  > 0 ? $usage['input_tokens']  : (int) ceil(strlen($fullResponse) / 4);
                    $tokensOut = $usage['output_tokens'] > 0 ? $usage['output_tokens'] : (int) ceil(strlen($fullResponse) / 4);
                    $total     = $usage['total_tokens']  > 0 ? $usage['total_tokens']  : ($tokensIn + $tokensOut);

                    // Parse buttons out of the full response before saving to DB
                    $parsed      = ChatButtonParser::parse($fullResponse);
                    $cleanedText = $parsed['message'];
                    $buttons     = $parsed['buttons'];

                    if (! empty($fullResponse)) {
                        ChatMessage::create([
                            'conversation_id' => $conversation->id,
                            'role'            => 'bot',
                            'message'         => $cleanedText,
                            'tokens_used'     => $tokensOut,
                            'created_at'      => now(),
                        ]);
                    }

                    $cost     = $grokService->estimateCost($total);
                    $usageLog = TokenUsageLog::getOrCreateForToday($client->id);
                    $usageLog->addUsage($tokensIn, $tokensOut, $cost);

                    $chatService->logGroqUsage($client->id, $conversation->id, [
                        'input_tokens'  => $tokensIn,
                        'output_tokens' => $tokensOut,
                        'tokens_used'   => $total,
                        'model'         => $grokService->getModel(),
                    ]);

                    echo 'data: '.json_encode([
                        'type'            => 'done',
                        'conversation_id' => $conversation->id,
                        'message'         => $cleanedText,
                        'buttons'         => $buttons,
                        'tokens'          => $total,
                        'time_ms'         => round(($endTime - $startTime) * 1000),
                    ])."\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                },
                function ($error) {
                    echo 'data: '.json_encode(['type' => 'error', 'message' => $error])."\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                }
            );
        }, 200, [
            'Content-Type'                 => 'text/event-stream',
            'Cache-Control'                => 'no-cache',
            'Connection'                   => 'keep-alive',
            'X-Accel-Buffering'            => 'no',
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

        return $parsed['scheme'].'://'.$parsed['host'].(isset($parsed['port']) ? ':'.$parsed['port'] : '');
    }
}
