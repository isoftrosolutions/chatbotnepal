<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widget_configs', function (Blueprint $table) {
            $table->boolean('prechat_enabled')->default(false)->after('show_powered_by');
        });
    }

    public function down(): void
    {
        Schema::table('widget_configs', function (Blueprint $table) {
            $table->dropColumn('prechat_enabled');
        });
    }
};
