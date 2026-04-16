<?php

namespace App\Providers;

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
    }
}
