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
            'welcome_message' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'primary_color_text' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'position' => 'required|in:bottom-right,bottom-left',
            'bot_name' => 'nullable|string|max:100',
            'show_powered_by' => 'boolean',
            'prechat_enabled' => 'boolean',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'watermark_enabled' => 'boolean',
            'watermark_opacity' => 'nullable|numeric|min:0.05|max:0.5',
            'watermark_position' => 'nullable|in:center,top-left,top-right,bottom-left,bottom-right',
        ]);

        // Handle logo upload
        $logoUrl = null;
        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('logos', 'public');
            $logoUrl = asset('storage/'.$logoPath);
        }

        // Determine primary color from color picker or text input
        $primaryColor = $validated['primary_color'] ?? $validated['primary_color_text'] ?? '#4F46E5';

        $configData = [
            'welcome_message' => $validated['welcome_message'] ?? 'Namaste! How can I help you today?',
            'primary_color' => $primaryColor,
            'position' => $validated['position'],
            'bot_name' => $validated['bot_name'] ?? 'Assistant',
            'show_powered_by' => $validated['show_powered_by'] ?? true,
            'prechat_enabled' => $validated['prechat_enabled'] ?? false,
            'watermark_enabled' => $validated['watermark_enabled'] ?? false,
            'watermark_opacity' => $validated['watermark_opacity'] ?? 0.1,
            'watermark_position' => $validated['watermark_position'] ?? 'center',
        ];

        // Only update logo if a new file was uploaded
        if ($logoUrl) {
            $configData['company_logo_url'] = $logoUrl;
        }

        $config = WidgetConfig::updateOrCreate(
            ['user_id' => $user->id],
            $configData
        );

        return redirect()->back()->with('success', 'Widget configuration saved');
    }
}
