<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WidgetConfig;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmbedController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $embedCode = '<script src="'.config('app.url').'/widget.js" data-token="'.$user->api_token.'"></script>';

        $config = $user->widgetConfig ?? (object) WidgetConfig::getDefaultConfig();

        return view('client.embed-code', compact('embedCode', 'config'));
    }

    public function updateConfig(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'welcome_message' => 'required|string|max:500',
            'primary_color'   => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'position'        => 'required|in:bottom-right,bottom-left',
            'bot_name'        => 'required|string|max:100',
            'show_powered_by' => 'boolean',
            'prechat_enabled' => 'boolean',
        ]);

        $config = WidgetConfig::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect()->back()->with('success', 'Widget configuration saved');
    }
}
