<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\KnowledgeBase;
use App\Models\Setting;
use App\Models\TokenUsageLog;
use App\Models\User;
use App\Models\WidgetSessionToken;
use App\Services\GrokService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StreamChatController extends Controller
{
    private GrokService $grokService;

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

    public function __construct(GrokService $grokService)
    {
        $this->grokService = $grokService;
    }

    public function stream(Request $request)
    {
        if (! $request->secure() && ! app()->environment('local')) {
            return response()->json(['error' => 'HTTPS required'], 403);
        }

        $request->validate([
            'site_id' => 'required|string',
            'message' => 'required|string|max:1000',
            'visitor_id' => 'nullable|string|max:64',
            'conversation_id' => 'nullable|integer',
            'source_url' => 'nullable|string|max:500',
        ]);

        $sessionToken = $request->header('X-Session-Token');
        $client = null;

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

        $conversation = $this->getOrCreateConversation($client, $request->conversation_id, $request->visitor_id, $this->sanitizeUrl($request->source_url));

        $visitorMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'visitor',
            'message' => $cleanedMessage,
            'tokens_used' => 0,
            'created_at' => now(),
        ]);

        $knowledgeBase = $this->buildKnowledgeBase($client);
        $conversationHistory = $this->getConversationHistory($conversation);
        $messages = $this->buildMessages($client, $knowledgeBase, $conversationHistory, $cleanedMessage);

        $fullResponse = '';
        $startTime = microtime(true);

        return response()->stream(function () use ($messages, $client, $conversation, $startTime) {
            $this->grokService->streamChat(
                $messages,
                function ($chunk) use (&$fullResponse) {
                    $fullResponse .= $chunk;
                    echo 'data: '.json_encode(['type' => 'chunk', 'content' => $chunk])."\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                },
                function () use (&$fullResponse, $client, $conversation, $startTime) {
                    $endTime = microtime(true);
                    $tokensUsed = (int) (strlen($fullResponse) / 4);
                    $inputTokens = $tokensUsed;
                    $outputTokens = $tokensUsed;

                    ChatMessage::create([
                        'conversation_id' => $conversation->id,
                        'role' => 'bot',
                        'message' => $fullResponse,
                        'tokens_used' => $tokensUsed,
                        'created_at' => now(),
                    ]);

                    $cost = $this->grokService->estimateCost($tokensUsed);
                    $usageLog = TokenUsageLog::getOrCreateForToday($client->id);
                    $usageLog->addUsage($inputTokens, $outputTokens, $cost);

                    echo 'data: '.json_encode([
                        'type' => 'done',
                        'conversation_id' => $conversation->id,
                        'tokens' => $tokensUsed,
                        'time_ms' => round(($endTime - $startTime) * 1000),
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
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function getOrCreateConversation(User $client, ?string $conversationId, ?string $visitorId, ?string $sourceUrl): ChatConversation
    {
        if ($conversationId) {
            $conversation = ChatConversation::where('id', $conversationId)
                ->where('user_id', $client->id)
                ->first();
            if ($conversation) {
                return $conversation;
            }
        }

        return ChatConversation::create([
            'user_id' => $client->id,
            'visitor_id' => $visitorId ?? 'unknown',
            'source_url' => $sourceUrl,
            'status' => 'active',
        ]);
    }

    private function buildKnowledgeBase(User $client): string
    {
        $files = KnowledgeBase::where('user_id', $client->id)
            ->where('is_active', true)
            ->get();

        if ($files->isEmpty()) {
            return 'No knowledge base available.';
        }

        return $files->map(fn ($file) => $file->content)->implode("\n\n---\n\n");
    }

    private function getConversationHistory(ChatConversation $conversation): Collection
    {
        return $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();
    }

    private function buildMessages(User $client, string $knowledgeBase, Collection $history, string $newMessage): array
    {
        $systemPrompt = $this->buildSystemPrompt($client, $knowledgeBase);
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg->role === 'bot' ? 'assistant' : 'user',
                'content' => $msg->message,
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $newMessage];

        return $messages;
    }

    private function buildSystemPrompt(User $client, string $knowledgeBase): string
    {
        $businessName = $client->company_name ?? $client->name ?? 'the business';
        $promptTemplate = Setting::get('grok_system_prompt',
            "You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base. Be friendly, concise, and helpful. If you don't know the answer, say so politely and suggest contacting the business directly."
        );

        $prompt = str_replace('{business_name}', $businessName, $promptTemplate);

        return $prompt."\n\n--- KNOWLEDGE BASE ---\n".$knowledgeBase."\n--- END KNOWLEDGE BASE ---";
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
