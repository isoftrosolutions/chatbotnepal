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

    // Daily midnight: mark invoices as overdue past grace period
    $schedule->call(function () {
        $graceDays = (int) Setting::get('auto_disable_after_days', 7);

        $overdue = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now()->subDays($graceDays))
            ->with('user')
            ->get();

        foreach ($overdue as $invoice) {
            $invoice->update(['status' => 'overdue']);
        }

        Log::info('Scheduler: checked overdue invoices', ['count' => $overdue->count()]);
    })->daily()->name('mark-overdue-invoices');

    // TODO: Wire up mail driver and send actual reminders. Currently only logs to laravel.log.
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
                'client' => $invoice->user?->email,
                'due' => $invoice->due_date,
            ]);
        }
    })->dailyAt('09:00')->name('billing-reminders');

    // Daily 00:10: Generate invoices for clients whose next_billing_date has arrived
    // Checks all active clients with valid billing setup and creates invoices when due
    $schedule->call(function () {
        $due = User::where('role', 'client')
            ->where('subscription_status', 'active')
            ->whereNotNull('monthly_amount')
            ->whereDate('next_billing_date', '<=', today())
            ->get();

        foreach ($due as $client) {
            Invoice::create([
                'user_id' => $client->id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'amount' => $client->monthly_amount,
                'type' => $client->billing_cycle,
                'billing_period_start' => today(),
                'billing_period_end' => today(),
                'status' => 'pending',
                'due_date' => today()->addDays(7),  // 7-day payment window
            ]);

            // Advance next_billing_date based on cycle
            $next = match($client->billing_cycle) {
                'monthly' => today()->addMonth(),
                'quarterly' => today()->addMonths(3),
                'yearly' => today()->addYear(),
            };
            $client->update(['next_billing_date' => $next]);
        }

        Log::info('Scheduler: generated due invoices', ['count' => $due->count()]);
    })->dailyAt('00:10')->name('generate-due-invoices');
});
