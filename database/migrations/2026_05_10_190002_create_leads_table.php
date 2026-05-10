<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel', 32);
            $table->json('lead_data')->nullable();
            $table->string('conversion_trigger', 64)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
