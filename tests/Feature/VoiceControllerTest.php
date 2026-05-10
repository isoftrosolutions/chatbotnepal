<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('grok_api_key', 'test-groq-key');
        Setting::set('gemini_api_key', 'test-gemini-key');
    }

    private function activeClient(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'client',
            'status' => 'active',
            'chatbot_enabled' => true,
            'voice_enabled' => true,
        ], $attrs));
    }

    private function fakeAudioFile(string $ext = 'webm'): UploadedFile
    {
        // Minimal valid file content — just needs to pass the file validator
        return UploadedFile::fake()->createWithContent(
            "audio.{$ext}",
            str_repeat("\x00", 512)
        );
    }

    // ─── /api/voice/transcribe ────────────────────────────────────────────────

    public function test_transcribe_returns_transcript_on_success(): void
    {
        $client = $this->activeClient();

        Http::fake([
            'api.groq.com/*' => Http::response(['text' => 'Hello world', 'language' => 'en'], 200),
        ]);

        $response = $this->post('/api/voice/transcribe', [
            'audio' => $this->fakeAudioFile(),
            'token' => $client->api_token,
        ]);

        $response->assertOk()->assertJson(['transcript' => 'Hello world', 'language' => 'en']);
    }

    public function test_transcribe_requires_audio_field(): void
    {
        $client = $this->activeClient();

        $response = $this->postJson('/api/voice/transcribe', [
            'token' => $client->api_token,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['audio']);
    }

    public function test_transcribe_requires_token_field(): void
    {
        $response = $this->postJson('/api/voice/transcribe', [
            'audio' => $this->fakeAudioFile(),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['token']);
    }

    public function test_transcribe_rejects_invalid_token(): void
    {
        Http::fake(); // should not be called

        $response = $this->post('/api/voice/transcribe', [
            'audio' => $this->fakeAudioFile(),
            'token' => 'not-a-real-token',
        ]);

        $response->assertStatus(401)->assertJson(['error' => 'invalid_token']);
        Http::assertNothingSent();
    }

    public function test_transcribe_rejects_suspended_client(): void
    {
        $client = $this->activeClient(['status' => 'suspended']);

        Http::fake();

        $response = $this->post('/api/voice/transcribe', [
            'audio' => $this->fakeAudioFile(),
            'token' => $client->api_token,
        ]);

        $response->assertStatus(403)->assertJson(['error' => 'account_suspended']);
        Http::assertNothingSent();
    }

    public function test_transcribe_returns_500_when_groq_key_missing(): void
    {
        $client = $this->activeClient();
        Config::set('services.groq.api_key', null);

        Http::fake();

        $response = $this->post('/api/voice/transcribe', [
            'audio' => $this->fakeAudioFile(),
            'token' => $client->api_token,
        ]);

        $response->assertStatus(500)->assertJson(['error' => 'transcription_failed']);
        Http::assertNothingSent();
    }

    public function test_transcribe_returns_500_on_groq_api_error(): void
    {
        $client = $this->activeClient();

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'rate_limit'], 429),
        ]);

        $response = $this->post('/api/voice/transcribe', [
            'audio' => $this->fakeAudioFile(),
            'token' => $client->api_token,
        ]);

        $response->assertStatus(500)->assertJson(['error' => 'transcription_failed']);
    }

    public function test_transcribe_rejects_disallowed_file_type(): void
    {
        $client = $this->activeClient();

        $response = $this->postJson('/api/voice/transcribe', [
            'audio' => UploadedFile::fake()->create('audio.exe', 100),
            'token' => $client->api_token,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['audio']);
    }

    public function test_transcribe_rejects_file_over_25mb(): void
    {
        $client = $this->activeClient();

        $response = $this->postJson('/api/voice/transcribe', [
            'audio' => UploadedFile::fake()->create('audio.webm', 26000), // 26 MB
            'token' => $client->api_token,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['audio']);
    }

    // ─── /api/voice/speak ────────────────────────────────────────────────────

    private function fakeGeminiAudioResponse(): array
    {
        $pcm = str_repeat("\x00\x01", 100); // minimal PCM data

        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['inlineData' => ['data' => base64_encode($pcm)]],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_speak_returns_audio_on_success(): void
    {
        $client = $this->activeClient();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->fakeGeminiAudioResponse(), 200),
        ]);

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello there.',
            'token' => $client->api_token,
        ]);

        $response->assertOk()->assertJsonStructure(['audio']);
        $this->assertNotEmpty($response->json('audio'));
    }

    public function test_speak_requires_text_field(): void
    {
        $client = $this->activeClient();

        $response = $this->postJson('/api/voice/speak', [
            'token' => $client->api_token,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['text']);
    }

    public function test_speak_requires_token_field(): void
    {
        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['token']);
    }

    public function test_speak_rejects_invalid_token(): void
    {
        Http::fake();

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => 'bad-token',
        ]);

        $response->assertStatus(401)->assertJson(['error' => 'invalid_token']);
        Http::assertNothingSent();
    }

    public function test_speak_rejects_suspended_client(): void
    {
        $client = $this->activeClient(['status' => 'suspended']);

        Http::fake();

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => $client->api_token,
        ]);

        $response->assertStatus(403)->assertJson(['error' => 'account_suspended']);
        Http::assertNothingSent();
    }

    public function test_speak_returns_500_when_gemini_key_missing(): void
    {
        $client = $this->activeClient();
        Config::set('services.gemini.api_key', null);

        Http::fake();

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => $client->api_token,
        ]);

        $response->assertStatus(500)->assertJson(['error' => 'tts_failed']);
        Http::assertNothingSent();
    }

    public function test_speak_returns_500_on_gemini_api_error(): void
    {
        $client = $this->activeClient();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'quota_exceeded'], 429),
        ]);

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => $client->api_token,
        ]);

        $response->assertStatus(500)->assertJson(['error' => 'tts_failed']);
    }

    public function test_speak_returns_500_when_gemini_returns_no_audio_data(): void
    {
        $client = $this->activeClient();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['candidates' => []], 200),
        ]);

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => $client->api_token,
        ]);

        $response->assertStatus(500)->assertJson(['error' => 'tts_failed']);
    }

    public function test_speak_rejects_text_over_5000_chars(): void
    {
        $client = $this->activeClient();

        $response = $this->postJson('/api/voice/speak', [
            'text' => str_repeat('a', 5001),
            'token' => $client->api_token,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['text']);
    }

    public function test_speak_uses_default_voice_when_client_voice_not_set(): void
    {
        $client = $this->activeClient(['voice' => null]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->fakeGeminiAudioResponse(), 200),
        ]);

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => $client->api_token,
        ]);

        $response->assertOk();

        Http::assertSent(function ($request) {
            $body = $request->data();

            return ($body['generationConfig']['speechConfig']['voiceConfig']['prebuiltVoiceConfig']['voiceName'] ?? null) === 'Kore';
        });
    }

    public function test_speak_uses_client_custom_voice_when_set(): void
    {
        $client = $this->activeClient(['voice' => 'Puck']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->fakeGeminiAudioResponse(), 200),
        ]);

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => $client->api_token,
        ]);

        $response->assertOk();

        Http::assertSent(function ($request) {
            $body = $request->data();

            return ($body['generationConfig']['speechConfig']['voiceConfig']['prebuiltVoiceConfig']['voiceName'] ?? null) === 'Puck';
        });
    }

    public function test_speak_wav_header_is_valid(): void
    {
        $client = $this->activeClient();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->fakeGeminiAudioResponse(), 200),
        ]);

        $response = $this->postJson('/api/voice/speak', [
            'text' => 'Hello.',
            'token' => $client->api_token,
        ]);

        $response->assertOk();
        $wav = base64_decode($response->json('audio'));

        // Check RIFF and WAVE markers
        $this->assertEquals('RIFF', substr($wav, 0, 4));
        $this->assertEquals('WAVE', substr($wav, 8, 4));
        $this->assertEquals('fmt ', substr($wav, 12, 4));
        $this->assertEquals('data', substr($wav, 36, 4));
    }
}
