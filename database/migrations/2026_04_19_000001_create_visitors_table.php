<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('visitor_uuid', 64);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('last_page_url', 500)->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'visitor_uuid']);
            $table->index(['user_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
