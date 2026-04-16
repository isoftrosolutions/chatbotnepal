<?php

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks (Laravel 11 — defined in console.php via app()->booted)
app()->booted(function () {
    $schedule = app(Schedule::class);

    // Daily midnight: disable chatbot for clients overdue past grace period
    $schedule->call(function () {
        $graceDays = (int) Setting::get('auto_disable_after_days', 7);

        $overdue = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now()->subDays($graceDays))
            ->with('user')
            ->get();

        foreach ($overdue as $invoice) {
            $invoice->update(['status' => 'overdue']);
            if ($invoice->user) {
                $invoice->user->update(['chatbot_enabled' => false]);
            }
        }

        Log::info('Scheduler: checked overdue invoices', ['count' => $overdue->count()]);
    })->daily()->name('disable-overdue-chatbots');

    // Daily 9 AM: log upcoming billing reminders
    $schedule->call(function () {
        $reminderDays = (int) Setting::get('billing_reminder_days', 3);

        $upcoming = Invoice::where('status', 'pending')
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays($reminderDays)])
            ->with('user')
            ->get();

        foreach ($upcoming as $invoice) {
            // TODO: send email/WhatsApp when mail driver is configured
            Log::info('Billing reminder due', [
                'invoice' => $invoice->invoice_number,
                'client'  => $invoice->user?->email,
                'due'     => $invoice->due_date,
            ]);
        }
    })->dailyAt('09:00')->name('billing-reminders');

    // Monthly on 1st: auto-generate invoices for all active clients
    $schedule->call(function () {
        $planPrices = [
            'basic'    => 1500,
            'standard' => 3000,
            'growth'   => 5000,
            'pro'      => 8000,
        ];

        $clients = User::where('role', 'client')
            ->where('status', 'active')
            ->get();

        foreach ($clients as $client) {
            $amount = $planPrices[$client->plan] ?? 1500;

            Invoice::create([
                'user_id'              => $client->id,
                'invoice_number'       => Invoice::generateInvoiceNumber(),
                'amount'               => $amount,
                'type'                 => 'monthly',
                'billing_period_start' => Carbon::now()->startOfMonth(),
                'billing_period_end'   => Carbon::now()->endOfMonth(),
                'status'               => 'pending',
                'due_date'             => Carbon::now()->addDays(7),
            ]);
        }

        Log::info('Scheduler: monthly invoices generated', ['count' => $clients->count()]);
    })->monthlyOn(1, '00:05')->name('generate-monthly-invoices');
});
