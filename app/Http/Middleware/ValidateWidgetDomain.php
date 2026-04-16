<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWidgetDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');

        $allowedDomains = $this->getAllowedDomains($request);

        $requestOrigin = $origin ?: ($referer ? parse_url($referer, PHP_URL_HOST) : null);

        if ($requestOrigin && ! in_array($requestOrigin, $allowedDomains)) {
            return response()->json([
                'success' => false,
                'error' => 'Domain not authorized',
            ], 403);
        }

        if (! $requestOrigin && ! app()->environment('local')) {
            return response()->json([
                'success' => false,
                'error' => 'Origin verification failed',
            ], 403);
        }

        return $next($request);
    }

    private function getAllowedDomains(Request $request): array
    {
        $token = $request->input('token') ?? $request->route('token');

        if ($token) {
            $user = User::where('api_token', $token)->first();
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
