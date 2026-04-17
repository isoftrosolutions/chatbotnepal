<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /*
     * Seeds placeholder rows for secrets configured via
     * `php artisan app:setup` on each server. Values here are intentionally
     * empty — never commit real keys/passwords to this file.
     */
    public function up(): void
    {
        $defaults = [
            // Groq AI
            'grok_api_key'      => '',
            'grok_model'        => 'llama-3.1-8b-instant',

            // SMTP
            'mail_host'         => 'smtp.gmail.com',
            'mail_port'         => '587',
            'mail_username'     => '',
            'mail_password'     => '',
            'mail_from_address' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'grok_api_key', 'grok_model',
            'mail_host', 'mail_port',
            'mail_username', 'mail_password', 'mail_from_address',
        ])->delete();
    }
};
