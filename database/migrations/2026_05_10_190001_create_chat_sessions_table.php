<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hosted_page_id')->nullable()->constrained('hosted_pages')->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('chat_conversations')->nullOnDelete();
            $table->string('channel', 32);
            $table->string('channel_ref')->nullable();
            $table->string('visitor_fingerprint', 191)->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->string('lead_status')->default('none');
            $table->json('meta')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'channel']);
            $table->index(['client_id', 'visitor_fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
