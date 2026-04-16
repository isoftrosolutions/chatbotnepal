<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'grok_model' => 'grok-3-mini',
            'grok_max_tokens' => '500',
            'grok_temperature' => '0.7',
            'grok_system_prompt' => "You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base. Be friendly, concise, and helpful. If you don't know the answer, say so politely and suggest contacting the business directly.",
            'platform_name' => 'ChatBot Nepal',
            'admin_email' => 'isoftrosolutions@gmail.com',
            'billing_reminder_days' => '3',
            'auto_disable_after_days' => '7',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
