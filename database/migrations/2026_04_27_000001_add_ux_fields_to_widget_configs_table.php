<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widget_configs', function (Blueprint $table) {
            $table->string('tagline', 200)->nullable()->after('bot_name');
            $table->string('privacy_policy_url', 500)->nullable()->after('tagline');
            $table->string('support_email', 191)->nullable()->after('privacy_policy_url');
            $table->boolean('message_meta_enabled')->default(false)->after('support_email');
            $table->json('suggested_questions')->nullable()->after('message_meta_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('widget_configs', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'privacy_policy_url', 'support_email', 'message_meta_enabled', 'suggested_questions']);
        });
    }
};
