<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WidgetConfig;
use Illuminate\Http\JsonResponse;

class WidgetConfigController extends Controller
{
    public function show(string $token): JsonResponse
    {
        $user = User::where('api_token', $token)->first();

        if (! $user || ! $user->chatbot_enabled) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        $config = $user->widgetConfig;

        if (! $config) {
            $config = WidgetConfig::create([
                'user_id' => $user->id,
                ...WidgetConfig::getDefaultConfig(),
            ]);
        }

        return response()->json([
            'business_name'        => $user->company_name ?? $user->name,
            'welcome_message'      => $config->welcome_message,
            'primary_color'        => $config->primary_color,
            'position'             => $config->position,
            'bot_name'             => $config->bot_name,
            'bot_avatar_url'       => $config->bot_avatar_url,
            'tagline'              => $config->tagline,
            'privacy_policy_url'   => $config->privacy_policy_url,
            'support_email'        => $config->support_email,
            'message_meta_enabled' => (bool) $config->message_meta_enabled,
            'show_powered_by'      => $config->show_powered_by,
            'prechat_enabled'      => $config->prechat_enabled,
            'company_logo_url'     => $config->company_logo_url,
            'watermark_enabled'    => $config->watermark_enabled,
            'watermark_opacity'    => $config->watermark_opacity,
            'watermark_position'   => $config->watermark_position,
            'suggested_questions'  => $config->suggested_questions ?? [],
        ]);
    }
}
