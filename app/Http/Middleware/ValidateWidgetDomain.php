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
        // Valid session token means the widget already authenticated — skip domain check
        $sessionToken = $request->header('X-Session-Token');
        if ($sessionToken) {
            $valid = WidgetSessionToken::where('token', $sessionToken)
                ->where('expires_at', '>', now())
                ->exists();
            if ($valid) {
                return $next($request);
            }
        }

        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');
        
        $requestHost = null;
        if ($origin) {
            $requestHost = parse_url($origin, PHP_URL_HOST);
        } elseif ($referer) {
            $requestHost = parse_url($referer, PHP_URL_HOST);
        }

        // If no host can be determined (e.g. non-browser call), allow it through
        if (! $requestHost) {
            return $next($request);
        }

        // Local development is always allowed
        if (in_array($requestHost, ['localhost', '127.0.0.1'])) {
            return $next($request);
        }

        $allowedDomains = $this->getAllowedDomains($request);

        if (! in_array($requestHost, $allowedDomains)) {
            return response()->json([
                'success' => false,
                'error' => 'Domain not authorized: ' . $requestHost,
            ], 403);
        }

        return $next($request);
    }

    private function getAllowedDomains(Request $request): array
    {
        $identifier = $request->input('token') ?? $request->route('token') ?? $request->input('site_id');

        if ($identifier) {
            $user = User::where('api_token', $identifier)
                ->orWhere('site_id', $identifier)
                ->first();

            if ($user && $user->website_url) {
                $url = $user->website_url;
                if (! str_contains($url, '://')) {
                    $url = 'https://' . $url;
                }
                
                $host = parse_url($url, PHP_URL_HOST);
                if ($host) {
                    $cleanHost = preg_replace('/^www\./', '', $host);
                    return [
                        $cleanHost,
                        'www.'.$cleanHost,
                    ];
                }
            }
        }

        return [];
    }
}
