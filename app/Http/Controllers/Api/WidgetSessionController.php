<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WidgetSessionToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetSessionController extends Controller
{
    public function createSession(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'nullable|string',
            'token' => 'nullable|string',
        ]);

        $identifier = $request->site_id ?? $request->token;

        if (! $identifier) {
            return response()->json(['error' => 'site_id or token required'], 400);
        }

        $user = User::where('site_id', $identifier)
            ->where('chatbot_enabled', true)
            ->where('status', 'active')
            ->first();

        if (! $user) {
            $user = User::where('api_token', $identifier)
                ->where('chatbot_enabled', true)
                ->where('status', 'active')
                ->first();
        }

        if (! $user) {
            return response()->json(['error' => 'Invalid site'], 404);
        }

        $sessionToken = WidgetSessionToken::createForUser($user);

        $config = $user->widgetConfig;

        return response()->json([
            'session_token' => $sessionToken->token,
            'expires_at' => $sessionToken->expires_at->toIso8601String(),
            'config' => [
                'business_name' => $user->company_name ?? $user->name,
                'welcome_message' => $config?->welcome_message ?? 'Namaste! How can I help you today?',
                'primary_color' => $config?->primary_color ?? '#4318FF',
                'position' => $config?->position ?? 'bottom-right',
                'bot_name' => $config?->bot_name ?? 'Assistant',
                'bot_avatar_url' => $config?->bot_avatar_url,
                'show_powered_by' => $config?->show_powered_by ?? true,
            ],
        ]);
    }

    public function verifySession(Request $request): JsonResponse
    {
        $request->validate([
            'session_token' => 'required|string',
        ]);

        $session = WidgetSessionToken::where('token', $request->session_token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $session) {
            return response()->json(['valid' => false], 401);
        }

        $session->touch();

        return response()->json([
            'valid' => true,
            'user_id' => $session->user_id,
        ]);
    }
}
