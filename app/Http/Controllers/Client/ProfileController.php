<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private OtpService $otp) {}

    // ── Profile ───────────────────────────────────────────────────────────────

    public function edit(): View
    {
        return view('client.profile', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'website_url'  => 'nullable|url|max:500',
        ]);

        // Email handled separately (requires OTP)
        $newEmail = $request->input('email');
        if ($newEmail && $newEmail !== $user->email) {
            $request->validate([
                'email' => 'required|email|max:255|unique:users,email',
            ]);

            if (! $this->otp->checkSendRateLimit($newEmail)) {
                return back()->withErrors(['email' => 'Too many verification emails sent. Please wait before trying again.'])->withInput();
            }

            // Delete any stale pending change for this user
            DB::table('pending_email_changes')->where('user_id', $user->id)->delete();

            $code = $this->otp->generate($newEmail, 'email_change');
            $this->otp->recordSendAttempt($newEmail);

            $otpRecord = OtpCode::where('email', $newEmail)->where('purpose', 'email_change')->latest()->first();

            DB::table('pending_email_changes')->insert([
                'user_id'    => $user->id,
                'new_email'  => $newEmail,
                'otp_id'     => $otpRecord->id,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
            ]);

            Mail::to($newEmail)->send(new OtpMail($code, 'email_change'));

            // Save the non-email fields first
            $user->update($validated);

            return redirect()->route('profile.email.verify.form')
                ->with('info', 'A verification code was sent to ' . $newEmail . '. Enter it below to confirm the change.');
        }

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    // ── Email change OTP ──────────────────────────────────────────────────────

    public function emailVerifyForm(): View
    {
        $pending = DB::table('pending_email_changes')
            ->where('user_id', auth()->id())
            ->where('expires_at', '>', now())
            ->first();

        abort_unless($pending, 404);

        return view('auth.verify-email-otp', ['pendingEmail' => $pending->new_email]);
    }

    public function emailVerify(Request $request): RedirectResponse
    {
        $request->validate(['otp' => 'required|string|digits:6']);

        $user    = auth()->user();
        $pending = DB::table('pending_email_changes')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pending) {
            return redirect()->route('profile.show')
                ->withErrors(['otp' => 'No pending email change found or it has expired.']);
        }

        $limitKey = 'email_change_verify:' . $user->id;
        if (RateLimiter::tooManyAttempts($limitKey, 5)) {
            return back()->withErrors(['otp' => 'Too many attempts. Please request a new verification code.']);
        }
        RateLimiter::hit($limitKey, 15 * 60);

        if (! $this->otp->verify($pending->new_email, 'email_change', $request->input('otp'))) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code.']);
        }

        // Check the new email is still available
        if (User::where('email', $pending->new_email)->where('id', '!=', $user->id)->exists()) {
            DB::table('pending_email_changes')->where('user_id', $user->id)->delete();
            return redirect()->route('profile.show')
                ->withErrors(['email' => 'That email address has already been taken.']);
        }

        $user->forceFill(['email' => $pending->new_email])->save();
        DB::table('pending_email_changes')->where('user_id', $user->id)->delete();
        RateLimiter::clear($limitKey);

        SecurityEvent::log('email_changed', $user->id, $request->ip(), $request->userAgent() ?? '');

        return redirect()->route('profile.show')->with('success', 'Email address updated successfully.');
    }

    // ── Password change ───────────────────────────────────────────────────────

    public function passwordEdit(): View
    {
        return view('client.password-change');
    }

    public function passwordUpdate(Request $request): RedirectResponse
    {
        $limitKey = 'pw_change:' . auth()->id();
        if (RateLimiter::tooManyAttempts($limitKey, 5)) {
            $seconds = RateLimiter::availableIn($limitKey);
            return back()->withErrors([
                'current_password' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $user = auth()->user();

        if (! password_verify($request->input('current_password'), $user->password)) {
            RateLimiter::hit($limitKey, 3600);
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->forceFill(['password' => $request->input('password')])->save();

        RateLimiter::clear($limitKey);
        SecurityEvent::log('password_changed', $user->id, $request->ip(), $request->userAgent() ?? '');

        // Revoke all other sessions
        Auth::logoutOtherDevices($request->input('password'));

        return back()->with('success', 'Password updated. All other sessions have been signed out.');
    }
}
