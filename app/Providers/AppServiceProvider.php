<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(30)->by($request->input('site_id', $request->ip()));
        });

        RateLimiter::for('session', function (Request $request) {
            return Limit::perMinute(10)->by($request->input('site_id', $request->ip()));
        });

        $this->applySmtpSettings();
    }

    private function applySmtpSettings(): void
    {
        try {
            $host = Setting::get('smtp_host');
            if (! $host) {
                return;
            }

            $encryption = Setting::get('smtp_encryption', 'tls');

            config([
                'mail.mailers.smtp.host'       => $host,
                'mail.mailers.smtp.port'        => (int) Setting::get('smtp_port', 587),
                'mail.mailers.smtp.encryption'  => $encryption === 'none' ? null : $encryption,
                'mail.mailers.smtp.username'    => Setting::get('smtp_username'),
                'mail.mailers.smtp.password'    => Setting::get('smtp_password'),
                'mail.from.address'             => Setting::get('mail_from_address'),
                'mail.from.name'                => Setting::get('mail_from_name', config('app.name')),
            ]);
        } catch (\Exception $e) {
            // Settings table may not exist during initial migration — fail silently
        }
    }
}
