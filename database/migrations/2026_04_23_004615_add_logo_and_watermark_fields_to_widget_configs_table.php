<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('widget_configs', function (Blueprint $table) {
            $table->string('company_logo_url')->nullable()->after('bot_avatar_url');
            $table->boolean('watermark_enabled')->default(false)->after('prechat_enabled');
            $table->decimal('watermark_opacity', 3, 2)->default(0.1)->after('watermark_enabled');
            $table->enum('watermark_position', ['center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'])->default('center')->after('watermark_opacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widget_configs', function (Blueprint $table) {
            $table->dropColumn(['company_logo_url', 'watermark_enabled', 'watermark_opacity', 'watermark_position']);
        });
    }
};
