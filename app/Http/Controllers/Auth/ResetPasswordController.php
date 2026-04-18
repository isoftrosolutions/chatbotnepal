<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'                 => 'required|email|max:255',
            'otp'                   => 'required|string|digits:6',
            'password'              => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $email = $request->input('email');

        // Per-email verification rate limit: 5 attempts per OTP lifetime
        $limitKey = 'pw_reset_verify:' . sha1($email);
        if (RateLimiter::tooManyAttempts($limitKey, 5)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['otp' => 'Too many attempts. Please request a new code.']);
        }
        RateLimiter::hit($limitKey, 15 * 60);

        if (! $this->otp->verify($email, 'password_reset', $request->input('otp'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['otp' => 'Invalid or expired verification code.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            // OTP verified but no user — shouldn't happen, but guard anyway
            return back()->withErrors(['email' => 'No account found with this email address.']);
        }

        $user->forceFill(['password' => $request->input('password')])->save();

        // Clear per-email rate limit on success
        RateLimiter::clear($limitKey);

        SecurityEvent::log('password_reset', $user->id, $request->ip(), $request->userAgent() ?? '');

        // Log the user in and redirect to their dashboard
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('password.success', [
            'redirect' => $user->isAdmin() ? route('admin.dashboard') : route('client.dashboard')
        ]);
    }
}
