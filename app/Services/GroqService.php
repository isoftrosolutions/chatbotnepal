<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    private string $apiKey;

    private string $apiUrl;

    private string $model;

    private int $maxTokens;

    private float $temperature;

    public function __construct()
    {
        $this->apiKey      = Setting::get('grok_api_key', env('GROQ_API_KEY', ''));
        $this->apiUrl      = Setting::get('groq_api_url', env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'));
        $this->model       = Setting::get('grok_model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));
        $this->maxTokens   = (int) Setting::get('grok_max_tokens', env('GROQ_MAX_TOKENS', 500));
        $this->temperature = (float) Setting::get('grok_temperature', env('GROQ_TEMPERATURE', 0.7));
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function chat(array $messages): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);

            if ($response->failed()) {
                $status   = $response->status();
                $body     = $response->body();
                $category = $this->categorizeError($status, $body);

                Log::error('Groq API error', [
                    'status'   => $status,
                    'category' => $category,
                    'body'     => substr($body, 0, 500),
                    'model'    => $this->model,
                ]);

                return [
                    'success'        => false,
                    'error'          => 'AI service temporarily unavailable',
                    'error_category' => $category,
                ];
            }

            $data  = $response->json();
            $usage = $data['usage'] ?? [];

            return [
                'success'       => true,
                'reply'         => $data['choices'][0]['message']['content'] ?? '',
                'tokens_used'   => $usage['total_tokens'] ?? 0,
                'input_tokens'  => $usage['prompt_tokens'] ?? 0,
                'output_tokens' => $usage['completion_tokens'] ?? 0,
                'model'         => $this->model,
            ];
        } catch (\Exception $e) {
            Log::error('Groq API exception', ['message' => $e->getMessage()]);

            return [
                'success'        => false,
                'error'          => 'AI service temporarily unavailable',
                'error_category' => 'network',
            ];
        }
    }

    private function categorizeError(int $status, string $body): string
    {
        if ($status === 401 || $status === 403) {
            return 'auth';
        }
        if ($status === 413 || $status === 429) {
            return 'rate_limit';
        }

        return 'server_error';
    }

    /**
     * Stream a chat response via SSE. $onComplete receives ['input_tokens', 'output_tokens', 'total_tokens']
     * sourced from the Groq usage chunk (stream_options.include_usage), falling back to 0 if unavailable.
     */
    public function streamChat(array $messages, callable $onChunk, callable $onComplete, callable $onError): void
    {
        try {
            $ch    = curl_init();
            $usage = [];

            $postData = json_encode([
                'model'          => $this->model,
                'messages'       => $messages,
                'max_tokens'     => $this->maxTokens,
                'temperature'    => $this->temperature,
                'stream'         => true,
                'stream_options' => ['include_usage' => true],
            ]);

            curl_setopt_array($ch, [
                CURLOPT_URL            => $this->apiUrl,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer '.$this->apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_WRITEFUNCTION  => function ($ch, $data) use ($onChunk, &$usage) {
                    foreach (explode("\n", $data) as $line) {
                        if (strpos($line, 'data: ') !== 0) {
                            continue;
                        }
                        $json = substr($line, 6);
                        if (trim($json) === '[DONE]') {
                            continue;
                        }
                        $decoded = json_decode($json, true);
                        if (! $decoded) {
                            continue;
                        }
                        // Groq sends a final chunk with usage when stream_options.include_usage is true
                        if (isset($decoded['usage'])) {
                            $usage = $decoded['usage'];
                        }
                        if (isset($decoded['choices'][0]['delta']['content'])) {
                            $onChunk($decoded['choices'][0]['delta']['content']);
                        }
                    }

                    return strlen($data);
                },
                CURLOPT_TIMEOUT => 60,
            ]);

            curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error('Groq streaming error', ['error' => $error]);
                $onError('Connection error');

                return;
            }

            $onComplete([
                'input_tokens'  => $usage['prompt_tokens'] ?? 0,
                'output_tokens' => $usage['completion_tokens'] ?? 0,
                'total_tokens'  => $usage['total_tokens'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Groq streaming exception', ['message' => $e->getMessage()]);
            $onError('AI service temporarily unavailable');
        }
    }

    public function estimateCost(int $totalTokens): float
    {
        $costPerToken = (float) Setting::get('cost_per_token', 0.00001);

        return $totalTokens * $costPerToken;
    }
}
