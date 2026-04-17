<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groq_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained('chat_conversations')->onDelete('set null');
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->string('model', 100)->default('');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groq_usage_logs');
    }
};
