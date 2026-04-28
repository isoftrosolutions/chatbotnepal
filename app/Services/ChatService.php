<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\GroqUsageLog;
use App\Models\KnowledgeBase;
use App\Models\Setting;
use App\Models\TokenUsageLog;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private GroqService $grokService;

    private const SYSTEM_TOKEN_BUDGET = 1500;
    private const TOTAL_TOKEN_BUDGET  = 4000;
    private const MAX_HISTORY         = 6;
    private const CHARS_PER_TOKEN     = 4;
    private const KB_PRIORITY         = ['faq' => 1, 'services' => 2, 'contact' => 3, 'about' => 4, 'custom' => 5];

    public function __construct(GroqService $grokService)
    {
        $this->grokService = $grokService;
    }

    public function processChat(
        User $client,
        string $message,
        ?int $conversationId = null,
        ?string $visitorId = null,
        ?string $sourceUrl = null,
        ?string $visitorName = null,
        ?string $visitorEmail = null,
        ?string $visitorPhone = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        if (! $client->chatbot_enabled) {
            return ['success' => false, 'error' => 'Chatbot is currently offline'];
        }

        if (! $client->isActive()) {
            return ['success' => false, 'error' => 'Chatbot is currently unavailable'];
        }

        $conversation = $this->getOrCreateConversation(
            $client, $conversationId, $visitorId, $sourceUrl,
            $visitorName, $visitorEmail, $visitorPhone
        );

        $this->upsertVisitor($client, $visitorId, $visitorName, $visitorEmail, $visitorPhone, $ipAddress, $userAgent, $sourceUrl);

        // Fetch history before saving visitor message so current turn isn't duplicated
        $messages = $this->buildTokenBudgetedMessages($client, $conversation, $message);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'visitor',
            'message'         => $message,
            'tokens_used'     => 0,
            'created_at'      => now(),
        ]);

        $result = $this->grokService->chat($messages);

        if (! $result['success']) {
            return $result;
        }

        if (! empty($result['reply'])) {
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role'            => 'bot',
                'message'         => $result['reply'],
                'tokens_used'     => $result['tokens_used'],
                'created_at'      => now(),
            ]);
        }

        $cost     = $this->grokService->estimateCost($result['tokens_used']);
        $usageLog = TokenUsageLog::getOrCreateForToday($client->id);
        $usageLog->addUsage($result['input_tokens'], $result['output_tokens'], $cost);

        $this->logGroqUsage($client->id, $conversation->id, $result);

        return [
            'success'         => true,
            'reply'           => $result['reply'] ?? '',
            'conversation_id' => $conversation->id,
        ];
    }

    /**
     * Build token-budgeted messages array for Groq.
     * Public so StreamChatController can reuse this without duplicating logic.
     * Call this BEFORE saving the visitor message to avoid duplicating it in history.
     */
    public function buildTokenBudgetedMessages(User $client, ChatConversation $conversation, string $newMessage): array
    {
        $knowledgeBase = $this->buildKnowledgeBase($client);
        $history       = $this->getConversationHistory($conversation);

        return $this->buildMessages($client, $knowledgeBase, $history, $newMessage);
    }

    public function getOrCreateConversation(
        User $client,
        ?int $conversationId,
        ?string $visitorId,
        ?string $sourceUrl,
        ?string $visitorName = null,
        ?string $visitorEmail = null,
        ?string $visitorPhone = null
    ): ChatConversation {
        if ($conversationId) {
            $conversation = ChatConversation::where('id', $conversationId)
                ->where('user_id', $client->id)
                ->first();
            if ($conversation) {
                $updates = [];
                if ($visitorName && ! $conversation->visitor_name)   $updates['visitor_name']  = $visitorName;
                if ($visitorEmail && ! $conversation->visitor_email)  $updates['visitor_email'] = $visitorEmail;
                if ($visitorPhone && ! $conversation->visitor_phone)  $updates['visitor_phone'] = $visitorPhone;
                if ($updates) $conversation->update($updates);
                return $conversation;
            }
        }

        return ChatConversation::create([
            'user_id'       => $client->id,
            'visitor_id'    => $visitorId ?? \Illuminate\Support\Str::uuid()->toString(),
            'visitor_name'  => $visitorName,
            'visitor_email' => $visitorEmail,
            'visitor_phone' => $visitorPhone,
            'source_url'    => $sourceUrl,
            'status'        => 'active',
        ]);
    }

    public function upsertVisitor(
        User $client,
        ?string $visitorId,
        ?string $name,
        ?string $email,
        ?string $phone,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $pageUrl
    ): void {
        if (! $visitorId || $visitorId === 'unknown') {
            return;
        }

        $visitor = Visitor::firstOrNew([
            'user_id'      => $client->id,
            'visitor_uuid' => $visitorId,
        ]);

        if (! $visitor->exists) {
            $visitor->first_seen_at = now();
        }

        if ($name)     $visitor->name          = $name;
        if ($email)    $visitor->email         = $email;
        if ($phone)    $visitor->phone         = $phone;
        if ($ipAddress) $visitor->ip_address   = $ipAddress;
        if ($userAgent) $visitor->user_agent   = $userAgent;
        if ($pageUrl)  $visitor->last_page_url = $pageUrl;
        $visitor->last_seen_at = now();
        $visitor->save();
    }

    public function logGroqUsage(int $userId, int $conversationId, array $result): void
    {
        try {
            GroqUsageLog::create([
                'user_id'           => $userId,
                'conversation_id'   => $conversationId,
                'prompt_tokens'     => $result['input_tokens'] ?? 0,
                'completion_tokens' => $result['output_tokens'] ?? 0,
                'total_tokens'      => $result['tokens_used'] ?? 0,
                'model'             => $result['model'] ?? $this->grokService->getModel(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log Groq usage', ['error' => $e->getMessage()]);
        }
    }

    private function buildKnowledgeBase(User $client): string
    {
        $files = KnowledgeBase::where('user_id', $client->id)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($f) => self::KB_PRIORITY[$f->file_type] ?? 99);

        if ($files->isEmpty()) {
            return "I don't have specific info about this business yet. Please contact them directly for details.";
        }

        $labels      = ['faq' => 'FAQ', 'services' => 'Services', 'contact' => 'Contact', 'about' => 'About', 'custom' => 'Info'];
        $budgetChars = (self::SYSTEM_TOKEN_BUDGET * self::CHARS_PER_TOKEN) - 300;
        $sections    = [];
        $usedChars   = 0;

        foreach ($files as $file) {
            $label       = $labels[$file->file_type] ?? 'Info';
            $cleaned     = $this->stripMarkdownNoise($file->content);
            $header      = "## {$label}\n";
            $sectionText = $header.$cleaned;

            if ($usedChars + strlen($sectionText) <= $budgetChars) {
                $sections[]  = $sectionText;
                $usedChars  += strlen($sectionText) + 2;
            } else {
                // Truncate by paragraphs — never cut mid-sentence
                $remaining = $budgetChars - $usedChars - strlen($header);
                if ($remaining > 100) {
                    $paragraphs = preg_split('/\n\n+/', $cleaned);
                    $truncated  = '';
                    foreach ($paragraphs as $para) {
                        if (strlen($truncated) + strlen($para) + 2 <= $remaining) {
                            $truncated .= ($truncated ? "\n\n" : '').$para;
                        } else {
                            break;
                        }
                    }
                    if ($truncated) {
                        $sections[] = $header.$truncated;
                    }
                }
                break;
            }
        }

        return implode("\n\n", $sections);
    }

    private function stripMarkdownNoise(string $content): string
    {
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        $content = preg_replace('/^#+\s*/m', '', $content);
        $content = preg_replace('/[ \t]+$/m', '', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }

    private function getConversationHistory(ChatConversation $conversation): Collection
    {
        return $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(self::MAX_HISTORY)
            ->get()
            ->reverse()
            ->values();
    }

    private function buildMessages(User $client, string $knowledgeBase, Collection $history, string $newMessage): array
    {
        $systemPrompt = $this->buildSystemPrompt($client, $knowledgeBase);

        $systemTokens  = $this->estimateTokens($systemPrompt);
        $userTokens    = $this->estimateTokens($newMessage);
        $historyBudget = self::TOTAL_TOKEN_BUDGET - $systemTokens - $userTokens - 200;

        // Iterate newest-first so we drop oldest turns when over budget
        $historyMessages = [];
        $historyTokens   = 0;
        foreach ($history->reverse() as $msg) {
            $tokens = $this->estimateTokens($msg->message);
            if ($historyTokens + $tokens <= $historyBudget) {
                $historyTokens += $tokens;
                array_unshift($historyMessages, [
                    'role'    => $msg->role === 'bot' ? 'assistant' : 'user',
                    'content' => $msg->message,
                ]);
            }
        }

        $messages   = [['role' => 'system', 'content' => $systemPrompt]];
        $messages   = array_merge($messages, $historyMessages);
        $messages[] = ['role' => 'user', 'content' => $newMessage];

        return $messages;
    }

    private function buildSystemPrompt(User $client, string $knowledgeBase): string
    {
        $businessName   = $client->company_name ?? $client->name ?? 'the business';
        $promptTemplate = Setting::get('grok_system_prompt',
            'You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base. Be friendly, concise, and helpful. If you don\'t know the answer, say so politely and suggest contacting the business directly.'
        );

        $prompt = str_replace('{business_name}', $businessName, $promptTemplate);

        $defaultButtonRules = <<<'RULES'

---
BUTTON FORMATTING RULES:
When it is helpful to offer the user clickable options, you MUST use these exact syntaxes:

1. LINK BUTTON (opens a URL in new tab):
   [link:Button Label|https://example.com]

   Examples:
   [link:Book Now|https://example.com/booking]
   [link:Call Us|tel:+9770000000000]
   [link:WhatsApp|https://wa.me/9770000000000]
   [link:Get Directions|https://maps.google.com/?q=Business+Name+City]
   [link:Our Facebook|https://facebook.com/page]

2. REPLY BUTTON (sends a message back to chat):
   [btn:Button Label|Message to send]

   Examples:
   [btn:Room Rates|What are your room rates?]
   [btn:Our Services|Tell me about your services]

RULES:
- Use LINK buttons whenever referring to a URL, phone number, map location, social media page, booking page, or any external resource from the knowledge base.
- Use REPLY buttons when offering the user follow-up topic choices.
- You can mix both types in a single response.
- Always use the ACTUAL URLs from the knowledge base above — never make up URLs.
- Place buttons AFTER your text explanation, not before.
- Maximum 4 buttons per response to avoid clutter.
- For phone numbers, use tel: prefix. For WhatsApp, use https://wa.me/ prefix.
- Keep button labels SHORT — 2-4 words maximum.
---
RULES;

        $stored      = Setting::get('grok_button_rules', '');
        $buttonRules = (is_string($stored) && trim($stored) !== '') ? $stored : $defaultButtonRules;

        return $prompt."\n\n--- KNOWLEDGE BASE ---\n".$knowledgeBase."\n--- END KNOWLEDGE BASE ---".$buttonRules;
    }

    private function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / self::CHARS_PER_TOKEN);
    }
}
