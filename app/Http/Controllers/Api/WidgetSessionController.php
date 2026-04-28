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
                'business_name'   => $user->company_name ?? $user->name,
                'welcome_message' => $config?->welcome_message ?? 'Namaste! How can I help you today?',
                'primary_color'   => $config?->primary_color ?? '#4318FF',
                'position'        => $config?->position ?? 'bottom-right',
                'bot_name'        => $config?->bot_name ?? 'Assistant',
                'bot_avatar_url'       => $config?->bot_avatar_url,
                'company_logo_url'    => $config?->company_logo_url,
                'tagline'             => $config?->tagline,
                'privacy_policy_url'  => $config?->privacy_policy_url,
                'support_email'       => $config?->support_email,
                'message_meta_enabled'=> (bool) ($config?->message_meta_enabled ?? false),
                'show_powered_by'     => $config?->show_powered_by ?? true,
                'prechat_enabled'     => $config?->prechat_enabled ?? false,
                'watermark_enabled'   => (bool) ($config?->watermark_enabled ?? false),
                'watermark_opacity'   => $config?->watermark_opacity ?? 0.1,
                'watermark_position'  => $config?->watermark_position ?? 'center',
                'suggested_questions' => $config?->suggested_questions ?? [],
                'welcome_buttons'     => $config?->welcome_buttons ?? [],
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

        $session->touchLastUsed();

        return response()->json([
            'valid' => true,
            'user_id' => $session->user_id,
        ]);
    }
}
