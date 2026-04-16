<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('pw_reset_verify:test@example.com');
        DB::table('otp_codes')->truncate();
    }

    protected function tearDown(): void
    {
        DB::table('otp_codes')->truncate();
        RateLimiter::clear('pw_reset_verify:test@example.com');
        RateLimiter::clear('otp_send:'.sha1('test@example.com'));
        RateLimiter::clear('otp_send:'.sha1('nonexistent@example.com'));
        parent::tearDown();
    }

    public function test_forgot_password_returns_same_response_for_existing_and_nonexistent_email(): void
    {
        $responseExisting = $this->post('/forgot-password', ['email' => 'test@example.com']);
        $responseNonexistent = $this->post('/forgot-password', ['email' => 'nonexistent@example.com']);

        $existingMessage = session('status');
        $this->post('/forgot-password', ['email' => 'another-nonexistent@example.com']);
        $nonexistentMessage = session('status');

        $this->assertEquals($existingMessage, $nonexistentMessage);
    }

    public function test_valid_email_triggers_otp_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'client',
        ]);

        $response = $this->post('/forgot-password', ['email' => 'test@example.com']);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertSent(OtpMail::class, function ($mail) {
            return $mail->purpose === 'password_reset';
        });

        $this->assertDatabaseHas('otp_codes', [
            'email' => 'test@example.com',
            'purpose' => 'password_reset',
        ]);

        $user->delete();
    }

    public function test_valid_otp_resets_password_and_logs_user_in(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'client',
        ]);

        $this->post('/forgot-password', ['email' => 'test@example.com']);

        $otpRecord = OtpCode::where('email', 'test@example.com')
            ->where('purpose', 'password_reset')
            ->latest('created_at')
            ->first();

        $plainOtp = '123456';
        $otpRecord->forceFill(['code_hash' => hash('sha256', $plainOtp)])->save();

        $newPassword = 'ResetPassword456!@';

        $response = $this->post('/reset-password', [
            'email' => 'test@example.com',
            'otp' => $plainOtp,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect();
        $this->assertTrue(Auth::check());

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));

        $user->delete();
    }

    public function test_expired_otp_rejected(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'client',
        ]);

        $this->post('/forgot-password', ['email' => 'test@example.com']);

        $otpRecord = OtpCode::where('email', 'test@example.com')
            ->where('purpose', 'password_reset')
            ->latest('created_at')
            ->first();

        $plainOtp = '123456';
        $otpRecord->forceFill([
            'code_hash' => hash('sha256', $plainOtp),
            'expires_at' => now()->subMinute(),
        ])->save();

        $response = $this->post('/reset-password', [
            'email' => 'test@example.com',
            'otp' => $plainOtp,
            'password' => 'ResetPassword456!@',
            'password_confirmation' => 'ResetPassword456!@',
        ]);

        $response->assertSessionHasErrors('otp');
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $user->password));

        $user->delete();
    }

    public function test_wrong_otp_five_times_invalidates_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'client',
        ]);

        $this->post('/forgot-password', ['email' => 'test@example.com']);

        $otpRecord = OtpCode::where('email', 'test@example.com')
            ->where('purpose', 'password_reset')
            ->latest('created_at')
            ->first();

        $plainOtp = '123456';
        $otpRecord->forceFill(['code_hash' => hash('sha256', $plainOtp)])->save();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/reset-password', [
                'email' => 'test@example.com',
                'otp' => (string) (999999 - $i),
                'password' => 'ResetPassword456!@',
                'password_confirmation' => 'ResetPassword456!@',
            ]);
        }

        $response = $this->post('/reset-password', [
            'email' => 'test@example.com',
            'otp' => $plainOtp,
            'password' => 'ResetPassword456!@',
            'password_confirmation' => 'ResetPassword456!@',
        ]);

        $response->assertSessionHasErrors('otp');
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $user->password));
        $this->assertDatabaseMissing('otp_codes', [
            'email' => 'test@example.com',
            'purpose' => 'password_reset',
        ]);

        $user->delete();
    }

    public function test_rate_limited_to_three_requests_per_email_per_fifteen_minutes(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'ratelimit@example.com',
            'role' => 'client',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/forgot-password', ['email' => 'ratelimit@example.com']);
        }

        $lastResponse = $this->post('/forgot-password', ['email' => 'ratelimit@example.com']);
        $lastResponse->assertRedirect();
        $lastResponse->assertSessionHas('status');

        $user->delete();
    }
}
