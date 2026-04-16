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
            'business_name' => $user->company_name ?? $user->name,
            'welcome_message' => $config->welcome_message,
            'primary_color' => $config->primary_color,
            'position' => $config->position,
            'bot_name' => $config->bot_name,
            'bot_avatar_url' => $config->bot_avatar_url,
            'show_powered_by' => $config->show_powered_by,
        ]);
    }
}
