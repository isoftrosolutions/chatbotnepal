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
            // Groq AI
            'grok_api_key'            => '',
            'groq_api_url'            => 'https://api.groq.com/openai/v1/chat/completions',
            'grok_model'              => 'llama-3.3-70b-versatile',
            'grok_max_tokens'         => '500',
            'grok_temperature'        => '0.7',
            'grok_system_prompt'      => "You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base. Be friendly, concise, and helpful. If you don't know the answer, say so politely and suggest contacting the business directly.",
            'grok_button_rules'       => '',
            // Platform
            'platform_name'           => 'ChatBot Nepal',
            'admin_email'             => '',
            // Payments
            'esewa_merchant_id'       => '',
            'esewa_env'               => 'test',
            'khalti_secret_key'       => '',
            'khalti_verify_url'       => 'https://khalti.com/api/v2/payment/verify/',
            // Billing
            'billing_reminder_days'   => '3',
            'auto_disable_after_days' => '7',
            // Plan prices
            'plan_price_starter'      => '1000',
            'plan_price_basic'        => '1500',
            'plan_price_standard'     => '3000',
            'plan_price_growth'       => '5000',
            'plan_price_pro'          => '10000',
            'plan_price_enterprise'   => '15000',
            'cost_per_token'          => '0.00001',
            // SMTP
            'smtp_host'               => '',
            'smtp_port'               => '587',
            'smtp_encryption'         => 'tls',
            'smtp_username'           => '',
            'smtp_password'           => '',
            'mail_from_address'       => '',
            'mail_from_name'          => 'ChatBot Nepal',
        ];

        $settings = array_merge($defaults, $settings);

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Groq AI
            'grok_api_key'            => 'nullable|string',
            'groq_api_url'            => 'required|url',
            'grok_model'              => 'required|string',
            'grok_max_tokens'         => 'required|integer|min:100|max:4000',
            'grok_temperature'        => 'required|numeric|min:0|max:2',
            'grok_system_prompt'      => 'required|string',
            'grok_button_rules'       => 'nullable|string',
            // Platform
            'platform_name'           => 'required|string|max:255',
            'admin_email'             => 'required|email',
            // Payments
            'esewa_merchant_id'       => 'nullable|string',
            'esewa_env'               => 'required|in:test,live',
            'khalti_secret_key'       => 'nullable|string',
            'khalti_verify_url'       => 'required|url',
            // Billing
            'billing_reminder_days'   => 'required|integer|min:1|max:30',
            'auto_disable_after_days' => 'required|integer|min:1|max:30',
            // Plan prices
            'plan_price_starter'      => 'required|numeric|min:0',
            'plan_price_basic'        => 'required|numeric|min:0',
            'plan_price_standard'     => 'required|numeric|min:0',
            'plan_price_growth'       => 'required|numeric|min:0',
            'plan_price_pro'          => 'required|numeric|min:0',
            'plan_price_enterprise'   => 'required|numeric|min:0',
            'cost_per_token'          => 'required|numeric|min:0',
            // SMTP
            'smtp_host'               => 'nullable|string|max:255',
            'smtp_port'               => 'required|integer|min:1|max:65535',
            'smtp_encryption'         => 'required|in:tls,ssl,none',
            'smtp_username'           => 'nullable|string|max:255',
            'smtp_password'           => 'nullable|string',
            'mail_from_address'       => 'nullable|email|max:255',
            'mail_from_name'          => 'required|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->back()
            ->with('success', 'Settings saved successfully');
    }
}
