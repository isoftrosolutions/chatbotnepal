<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosted_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('status')->default('active');
            $table->json('public_config')->nullable();
            $table->json('behavior_config')->nullable();
            $table->string('custom_domain')->nullable();
            $table->string('domain_verification_token')->nullable();
            $table->timestamp('domain_verified_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosted_pages');
    }
};
