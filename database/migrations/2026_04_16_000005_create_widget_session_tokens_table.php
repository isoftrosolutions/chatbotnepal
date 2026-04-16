<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_session_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('last_used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->index(['user_id', 'expires_at']);
            $table->index('expires_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('site_id', 32)->nullable()->after('api_token');
            $table->unique('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_session_tokens');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('site_id');
        });
    }
};
