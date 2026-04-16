<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GrokService
{
    private string $apiKey;

    private string $apiUrl;

    private string $model;

    private int $maxTokens;

    private float $temperature;

    public function __construct()
    {
        $this->apiKey = Setting::get('grok_api_key', env('GROK_API_KEY', ''));
        $this->apiUrl = env('GROK_API_URL', 'https://api.x.ai/v1/chat/completions');
        $this->model = Setting::get('grok_model', env('GROK_MODEL', 'grok-3-mini'));
        $this->maxTokens = (int) Setting::get('grok_max_tokens', env('GROK_MAX_TOKENS', 500));
        $this->temperature = (float) Setting::get('grok_temperature', env('GROK_TEMPERATURE', 0.7));
    }

    public function chat(array $messages): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);

            if ($response->failed()) {
                Log::error('Grok API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'AI service temporarily unavailable',
                ];
            }

            $data = $response->json();
            $usage = $data['usage'] ?? [];

            return [
                'success' => true,
                'reply' => $data['choices'][0]['message']['content'] ?? '',
                'tokens_used' => $usage['total_tokens'] ?? 0,
                'input_tokens' => $usage['prompt_tokens'] ?? 0,
                'output_tokens' => $usage['completion_tokens'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Grok API exception', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'AI service temporarily unavailable',
            ];
        }
    }

    public function estimateCost(int $totalTokens): float
    {
        $costPerToken = 0.00001;

        return $totalTokens * $costPerToken;
    }
}
