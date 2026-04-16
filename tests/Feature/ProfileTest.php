<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'client', 'password' => Hash::make('OldPassword123!')]);
    }

    protected function tearDown(): void
    {
        DB::table('pending_email_changes')->truncate();
        DB::table('otp_codes')->truncate();
        RateLimiter::clear('email_change_verify:'.$this->user->id);
        parent::tearDown();
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_user_can_update_name(): void
    {
        $this->actingAs($this->user);

        $response = $this->withHeaders(['referer' => 'http://localhost/profile'])
            ->patch('/profile', [
                'name' => 'New Name',
            ]);

        $response->assertRedirect('/profile');
        $this->user->refresh();
        $this->assertEquals('New Name', $this->user->name);
    }

    public function test_email_change_sends_otp_does_not_update_immediately(): void
    {
        Mail::fake();

        $newEmail = 'newemail@example.com';

        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => $this->user->name,
            'email' => $newEmail,
        ]);

        $response->assertRedirect('/profile/email/verify');

        $this->user->refresh();
        $this->assertNotEquals($newEmail, $this->user->email);

        Mail::assertSent(OtpMail::class);

        $this->assertDatabaseHas('pending_email_changes', [
            'user_id' => $this->user->id,
            'new_email' => $newEmail,
        ]);
    }

    public function test_duplicate_email_rejected(): void
    {
        $otherUser = User::factory()->create(['email' => 'taken@example.com', 'role' => 'client']);

        $response = $this->actingAs($this->user)->patch('/profile', [
            'name' => $this->user->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $otherUser->delete();
    }

    public function test_valid_otp_commits_email_change(): void
    {
        Mail::fake();

        $newEmail = 'newemail@example.com';

        $this->actingAs($this->user)->patch('/profile', [
            'name' => $this->user->name,
            'email' => $newEmail,
        ]);

        $otpRecord = OtpCode::where('email', $newEmail)
            ->where('purpose', 'email_change')
            ->latest('created_at')
            ->first();

        $plainOtp = '123456';
        $otpRecord->forceFill(['code_hash' => hash('sha256', $plainOtp)])->save();

        $response = $this->actingAs($this->user)->post('/profile/email/verify', [
            'otp' => $plainOtp,
        ]);

        $response->assertRedirect('/profile');
        $this->user->refresh();
        $this->assertEquals($newEmail, $this->user->email);
        $this->assertDatabaseMissing('pending_email_changes', ['user_id' => $this->user->id]);
    }

    public function test_invalid_otp_rejected(): void
    {
        Mail::fake();

        $newEmail = 'newemail2@example.com';

        $this->actingAs($this->user)->patch('/profile', [
            'name' => $this->user->name,
            'email' => $newEmail,
        ]);

        $otpRecord = OtpCode::where('email', $newEmail)
            ->where('purpose', 'email_change')
            ->latest('created_at')
            ->first();

        $plainOtp = '123456';
        $otpRecord->forceFill(['code_hash' => hash('sha256', $plainOtp)])->save();

        $response = $this->actingAs($this->user)->post('/profile/email/verify', [
            'otp' => '000000',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->user->refresh();
        $this->assertNotEquals($newEmail, $this->user->email);
    }
}
