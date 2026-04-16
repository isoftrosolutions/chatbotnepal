<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    private const EXPIRY_MINUTES  = 15;
    private const MAX_ATTEMPTS    = 5;
    private const MAX_SENDS       = 3;
    private const SEND_WINDOW     = 15; // minutes

    /**
     * Generate a new 6-digit OTP, store it hashed, and return the plain code.
     * Deletes any existing unexpired OTP for the same email+purpose before creating.
     */
    public function generate(string $email, string $purpose): string
    {
        // Delete any previous OTP for this email+purpose
        OtpCode::where('email', $email)->where('purpose', $purpose)->delete();

        $plain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'email'      => $email,
            'code_hash'  => hash('sha256', $plain),
            'purpose'    => $purpose,
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        return $plain;
    }

    /**
     * Verify a plain OTP code.  Returns true on success, false on failure.
     * Increments attempt counter; invalidates after MAX_ATTEMPTS.
     */
    public function verify(string $email, string $purpose, string $plain): bool
    {
        $otp = OtpCode::where('email', $email)
            ->where('purpose', $purpose)
            ->latest('created_at')
            ->first();

        if (! $otp) {
            return false;
        }

        if ($otp->isExpired() || $otp->isExhausted()) {
            $otp->delete();
            return false;
        }

        $otp->increment('attempts');

        // Constant-time compare to prevent timing attacks
        if (! hash_equals($otp->code_hash, hash('sha256', $plain))) {
            if ($otp->fresh()->isExhausted()) {
                $otp->delete();
            }
            return false;
        }

        // Single-use: delete immediately on success
        $otp->delete();
        return true;
    }

    /**
     * Check if the send rate limit is exceeded for this email.
     * Returns true if they are within the limit (allowed to send).
     */
    public function checkSendRateLimit(string $email): bool
    {
        $key = 'otp_send:' . sha1($email);
        return ! RateLimiter::tooManyAttempts($key, self::MAX_SENDS);
    }

    /**
     * Record a send attempt for rate-limiting purposes.
     */
    public function recordSendAttempt(string $email): void
    {
        $key = 'otp_send:' . sha1($email);
        RateLimiter::hit($key, self::SEND_WINDOW * 60);
    }

    /**
     * Seconds until the send rate limit resets.
     */
    public function sendRetryAfter(string $email): int
    {
        return RateLimiter::availableIn('otp_send:' . sha1($email));
    }
}
