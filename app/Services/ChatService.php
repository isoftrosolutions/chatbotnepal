<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\KnowledgeBase;
use App\Models\Setting;
use App\Models\TokenUsageLog;
use App\Models\User;
use Illuminate\Support\Collection;

class ChatService
{
    private GrokService $grokService;

    public function __construct(GrokService $grokService)
    {
        $this->grokService = $grokService;
    }

    public function processChat(
        User $client,
        string $message,
        ?string $conversationId = null,
        ?string $visitorId = null,
        ?string $sourceUrl = null
    ): array {
        if (! $client->chatbot_enabled) {
            return [
                'success' => false,
                'error' => 'Chatbot is currently offline',
            ];
        }

        if (! $client->isActive()) {
            return [
                'success' => false,
                'error' => 'Chatbot is currently unavailable',
            ];
        }

        $conversation = $this->getOrCreateConversation($client, $conversationId, $visitorId, $sourceUrl);

        $knowledgeBase = $this->buildKnowledgeBase($client);
        $conversationHistory = $this->getConversationHistory($conversation);

        $visitorMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'visitor',
            'message' => $message,
            'tokens_used' => 0,
            'created_at' => now(),
        ]);

        $messages = $this->buildMessages($client, $knowledgeBase, $conversationHistory, $message);
        $result = $this->grokService->chat($messages);

        if (! $result['success']) {
            return $result;
        }

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'bot',
            'message' => $result['reply'],
            'tokens_used' => $result['tokens_used'],
            'created_at' => now(),
        ]);

        $cost = $this->grokService->estimateCost($result['tokens_used']);
        $usageLog = TokenUsageLog::getOrCreateForToday($client->id);
        $usageLog->addUsage($result['input_tokens'], $result['output_tokens'], $cost);

        return [
            'success' => true,
            'reply' => $result['reply'],
            'conversation_id' => $conversation->id,
        ];
    }

    private function getOrCreateConversation(
        User $client,
        ?string $conversationId,
        ?string $visitorId,
        ?string $sourceUrl
    ): ChatConversation {
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

    private function buildMessages(
        User $client,
        string $knowledgeBase,
        Collection $history,
        string $newMessage
    ): array {
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
}
