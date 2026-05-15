<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan_name', 100)->nullable()->after('voice');
            $table->decimal('monthly_amount', 10, 2)->nullable()->after('plan_name');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly')->after('monthly_amount');
            $table->date('next_billing_date')->nullable()->after('billing_cycle');
            $table->timestamp('subscription_started_at')->nullable()->after('next_billing_date');
            $table->enum('subscription_status', ['active', 'paused', 'cancelled'])->default('active')->after('subscription_started_at');
        });

        // Data migration for existing clients
        $planPrices = [
            'basic' => 1500,
            'standard' => 3000,
            'growth' => 5000,
            'pro' => 8000,
        ];

        $clients = DB::table('users')->where('role', 'client')->get();

        foreach ($clients as $client) {
            $planName = ucfirst($client->plan ?? 'basic');
            $amount = isset($planPrices[$client->plan]) ? $planPrices[$client->plan] : null;
            $status = ($client->chatbot_enabled ?? true) ? 'active' : 'paused';

            DB::table('users')->where('id', $client->id)->update([
                'plan_name' => $planName,
                'monthly_amount' => $amount,
                'billing_cycle' => 'monthly',
                'next_billing_date' => now()->addMonth()->startOfMonth(),
                'subscription_started_at' => $client->created_at,
                'subscription_status' => $status,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'plan_name',
                'monthly_amount',
                'billing_cycle',
                'next_billing_date',
                'subscription_started_at',
                'subscription_status',
            ]);
        });
    }
};
