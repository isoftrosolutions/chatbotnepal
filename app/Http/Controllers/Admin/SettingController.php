<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        $defaults = [
            'grok_api_key' => '',
            'grok_model' => 'grok-3-mini',
            'grok_max_tokens' => '500',
            'grok_temperature' => '0.7',
            'grok_system_prompt' => "You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base. Be friendly, concise, and helpful. If you don't know the answer, say so politely and suggest contacting the business directly.",
            'platform_name' => 'ChatBot Nepal',
            'admin_email' => 'isoftrosolutions@gmail.com',
            'esewa_merchant_id' => '',
            'khalti_secret_key' => '',
            'billing_reminder_days' => '3',
            'auto_disable_after_days' => '7',
        ];

        $settings = array_merge($defaults, $settings);

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grok_api_key' => 'nullable|string',
            'grok_model' => 'required|string',
            'grok_max_tokens' => 'required|integer|min:100|max:2000',
            'grok_temperature' => 'required|numeric|min:0|max:2',
            'grok_system_prompt' => 'required|string',
            'platform_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'esewa_merchant_id' => 'nullable|string',
            'khalti_secret_key' => 'nullable|string',
            'billing_reminder_days' => 'required|integer|min:1|max:30',
            'auto_disable_after_days' => 'required|integer|min:1|max:30',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->back()
            ->with('success', 'Settings saved successfully');
    }
}
