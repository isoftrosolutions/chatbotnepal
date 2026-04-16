<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email|max:255']);

        $email = $request->input('email');

        // Always respond identically regardless of whether the email exists
        // (prevents user enumeration)
        $user = User::where('email', $email)->first();

        if ($user) {
            if (! $this->otp->checkSendRateLimit($email)) {
                // Still return success message — don't reveal rate limit per-email
                return back()->with('status', 'If that email is registered, a verification code has been sent.');
            }

            $code = $this->otp->generate($email, 'password_reset');
            $this->otp->recordSendAttempt($email);

            Mail::to($email)->send(new OtpMail($code, 'password_reset'));
        }

        return back()->with('status', 'If that email is registered, a verification code has been sent.');
    }
}
