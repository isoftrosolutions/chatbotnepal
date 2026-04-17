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
        $this->apiKey = Setting::get('grok_api_key', env('GROQ_API_KEY', env('GROK_API_KEY', '')));
        $this->apiUrl = env('GROQ_API_URL', env('GROK_API_URL', 'https://api.groq.com/openai/v1/chat/completions'));
        $this->model = Setting::get('grok_model', env('GROQ_MODEL', env('GROK_MODEL', 'llama3-8b-8192')));
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

    public function streamChat(array $messages, callable $onChunk, callable $onComplete, callable $onError): void
    {
        try {
            $ch = curl_init();

            $postData = json_encode([
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'stream' => true,
            ]);

            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer '.$this->apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_WRITEFUNCTION => function ($ch, $data) use ($onChunk) {
                    $lines = explode("\n", $data);
                    foreach ($lines as $line) {
                        if (strpos($line, 'data: ') === 0) {
                            $json = substr($line, 6);
                            if (trim($json) === '[DONE]') {
                                continue;
                            }
                            $decoded = json_decode($json, true);
                            if (isset($decoded['choices'][0]['delta']['content'])) {
                                $content = $decoded['choices'][0]['delta']['content'];
                                $onChunk($content);
                            }
                        }
                    }

                    return strlen($data);
                },
                CURLOPT_TIMEOUT => 60,
            ]);

            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error('Grok streaming error', ['error' => $error]);
                $onError('Connection error');

                return;
            }

            $onComplete();
        } catch (\Exception $e) {
            Log::error('Grok streaming exception', ['message' => $e->getMessage()]);
            $onError('AI service temporarily unavailable');
        }
    }

    public function estimateCost(int $totalTokens): float
    {
        $costPerToken = 0.00001;

        return $totalTokens * $costPerToken;
    }
}
