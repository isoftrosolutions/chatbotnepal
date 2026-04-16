<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'client',
            'password' => Hash::make('OldPassword123!'),
        ]);
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('pw_change:'.$this->user->id);
        parent::tearDown();
    }

    public function test_authenticated_user_can_view_password_change_page(): void
    {
        $response = $this->actingAs($this->user)->get('/profile/password');

        $response->assertStatus(200);
        $response->assertSee('Current Password');
        $response->assertSee('New Password');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/profile/password');

        $response->assertRedirect('/login');
    }

    public function test_wrong_current_password_rejected(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewPassword456!@',
            'password_confirmation' => 'NewPassword456!@',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $this->user->password));
    }

    public function test_weak_password_rejected(): void
    {
        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ]);

        $response->assertSessionHasErrors('password');
        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $this->user->password));
    }

    public function test_valid_password_change_succeeds(): void
    {
        $newPassword = 'NewSecurePass99!';

        $response = $this->actingAs($this->user)
            ->withHeaders(['referer' => 'http://localhost/profile/password'])
            ->patch('/profile/password', [
                'current_password' => 'OldPassword123!',
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

        $response->assertRedirect('/profile/password');
        $response->assertSessionHas('success');
        $this->user->refresh();
        $this->assertTrue(Hash::check($newPassword, $this->user->password));
    }

    public function test_rate_limited_after_five_failed_attempts(): void
    {
        RateLimiter::clear('pw_change:'.$this->user->id);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->user)->patch('/profile/password', [
                'current_password' => 'WrongPassword'.$i.'!',
                'password' => 'NewPassword123!@',
                'password_confirmation' => 'NewPassword123!@',
            ]);
        }

        $response = $this->actingAs($this->user)->patch('/profile/password', [
            'current_password' => 'WrongPasswordFinal!',
            'password' => 'NewPassword123!@',
            'password_confirmation' => 'NewPassword123!@',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertStringContainsString('Too many attempts', session('errors')->first('current_password'));
    }
}
