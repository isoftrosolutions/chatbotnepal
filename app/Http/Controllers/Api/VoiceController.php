<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceController extends Controller
{
    public function transcribe(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,mpeg,mp4,wav,flac,m4a,mp3|max:25600', // 25MB max
            'token' => 'required|string',
        ]);

        try {
            $client = User::where('api_token', $request->token)->first();
            if (! $client) {
                return response()->json(['error' => 'invalid_token'], 401);
            }

            if (! $client->isActive()) {
                return response()->json(['error' => 'account_suspended'], 403);
            }

            $audioFile = $request->file('audio');
            $audioContent = file_get_contents($audioFile->getRealPath());

            $groqKey = Setting::get('grok_api_key', config('services.groq.api_key', ''));
            if (! $groqKey) {
                Log::error('Voice transcribe: GROQ_API_KEY not configured');

                return response()->json(['error' => 'transcription_failed'], 500);
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$groqKey,
                ])
                ->attach('file', $audioContent, 'audio.webm')
                ->post('https://api.groq.com/openai/v1/audio/transcriptions', [
                    'model' => 'whisper-large-v3',
                    'response_format' => 'json',
                ]);

            if (! $response->successful()) {
                Log::error('Groq Whisper API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json(['error' => 'transcription_failed'], 500);
            }

            $data = $response->json();

            return response()->json([
                'transcript' => $data['text'] ?? '',
                'language' => $data['language'] ?? 'en',
            ]);

        } catch (\Exception $e) {
            Log::error('Voice transcribe exception', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'transcription_failed'], 500);
        }
    }

    public function speak(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'token' => 'required|string',
        ]);

        try {
            $client = User::where('api_token', $request->token)->first();
            if (! $client) {
                return response()->json(['error' => 'invalid_token'], 401);
            }

            if (! $client->isActive()) {
                return response()->json(['error' => 'account_suspended'], 403);
            }

            $text = $request->text;

            $geminiKey = Setting::get('gemini_api_key', config('services.gemini.api_key', ''));
            if (! $geminiKey) {
                Log::error('Voice speak: GEMINI_API_KEY not configured');

                return response()->json(['error' => 'tts_failed'], 500);
            }

            $voice = $client->voice ?? 'Kore'; // Default to Kore, can be overridden per client

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-goog-api-key' => $geminiKey,
                ])
                ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-tts:generateContent', [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $text,
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'speechConfig' => [
                            'voiceConfig' => [
                                'prebuiltVoiceConfig' => [
                                    'voiceName' => $voice,
                                ],
                            ],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Gemini TTS API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json(['error' => 'tts_failed'], 500);
            }

            $data = $response->json();
            $audioBase64 = $data['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? null;

            if (! $audioBase64) {
                Log::error('Gemini TTS: No audio data in response');

                return response()->json(['error' => 'tts_failed'], 500);
            }

            // Convert base64 PCM to WAV
            $pcmData = base64_decode($audioBase64);
            $wavData = $this->createWavFromPcm($pcmData);

            return response()->json([
                'audio' => base64_encode($wavData),
            ]);

        } catch (\Exception $e) {
            Log::error('Voice speak exception', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'tts_failed'], 500);
        }
    }

    private function createWavFromPcm(string $pcmData): string
    {
        // PCM 16bit, 24000Hz, 1 channel (mono)
        $sampleRate = 24000;
        $bitsPerSample = 16;
        $numChannels = 1;
        $byteRate = $sampleRate * $numChannels * ($bitsPerSample / 8);
        $blockAlign = $numChannels * ($bitsPerSample / 8);
        $dataSize = strlen($pcmData);

        // WAV header (44 bytes)
        $header = pack(
            'A4VA4A4VvvVVvvA4V',
            'RIFF',                    // ChunkID
            36 + $dataSize,           // ChunkSize
            'WAVE',                   // Format
            'fmt ',                   // Subchunk1ID
            16,                       // Subchunk1Size
            1,                        // AudioFormat (PCM)
            $numChannels,             // NumChannels
            $sampleRate,              // SampleRate
            $byteRate,                // ByteRate
            $blockAlign,              // BlockAlign
            $bitsPerSample,           // BitsPerSample
            'data',                   // Subchunk2ID
            $dataSize                 // Subchunk2Size
        );

        return $header.$pcmData;
    }
}
