<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\WidgetSessionToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWidgetDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');

        if ($request->is('api/widget/*') || $request->is('api/widget/session')) {
            $response = $next($request);

            return $response->withHeaders([
                'Access-Control-Allow-Origin' => $origin ?: '*',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }

        // Valid session token means the widget already authenticated — skip domain check
        $sessionToken = $request->header('X-Session-Token');
        if ($sessionToken) {
            $valid = WidgetSessionToken::where('token', $sessionToken)
                ->where('expires_at', '>', now())
                ->exists();
            if ($valid) {
                $response = $next($request);
                return $response->withHeaders([
                    'Access-Control-Allow-Origin' => $origin ?: '*',
                    'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Session-Token',
                    'Access-Control-Allow-Credentials' => 'true',
                ]);
            }
        }

        $allowedDomains = $this->getAllowedDomains($request);

        $requestOrigin = $origin ?: ($referer ? parse_url($referer, PHP_URL_HOST) : null);

        if ($requestOrigin && ! in_array($requestOrigin, $allowedDomains)) {
            return response()->json([
                'success' => false,
                'error' => 'Domain not authorized',
            ], 403)->withHeaders([
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Session-Token',
            ]);
        }

        if (! $requestOrigin && ! app()->environment('local')) {
            return response()->json([
                'success' => false,
                'error' => 'Origin verification failed',
            ], 403);
        }

        $response = $next($request);

        return $response->withHeaders([
            'Access-Control-Allow-Origin' => $origin ?: '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Session-Token',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }

    private function getAllowedDomains(Request $request): array
    {
        $identifier = $request->input('token') ?? $request->route('token') ?? $request->input('site_id');

        if ($identifier) {
            $user = User::where('api_token', $identifier)
                ->orWhere('site_id', $identifier)
                ->first();

            if ($user && $user->website_url) {
                $parsed = parse_url($user->website_url);
                $host = $parsed['host'] ?? null;
                if ($host) {
                    return [
                        $host,
                        'www.'.$host,
                        preg_replace('/^www\./', '', $host),
                    ];
                }
            }
        }

        return [
            'localhost',
            '127.0.0.1',
        ];
    }
}
