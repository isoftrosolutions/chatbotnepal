<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('welcome_message')->default('Namaste! How can I help you today?');
            $table->string('primary_color', 7)->default('#4F46E5');
            $table->enum('position', ['bottom-right', 'bottom-left'])->default('bottom-right');
            $table->string('bot_name', 100)->default('Assistant');
            $table->string('bot_avatar_url', 500)->nullable();
            $table->boolean('show_powered_by')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_configs');
    }
};
