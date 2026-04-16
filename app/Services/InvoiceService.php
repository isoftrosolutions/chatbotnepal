<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createInvoice(User $user, string $type, float $amount, ?Carbon $billingPeriodStart = null, ?Carbon $billingPeriodEnd = null): Invoice
    {
        $billingPeriodStart = $billingPeriodStart ?? Carbon::now();
        $billingPeriodEnd = $billingPeriodEnd ?? Carbon::now()->addMonth();

        return Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $amount,
            'type' => $type,
            'billing_period_start' => $billingPeriodStart,
            'billing_period_end' => $billingPeriodEnd,
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(7),
        ]);
    }

    public function createMonthlyInvoice(User $user): Invoice
    {
        $amount = $this->getPlanAmount($user->plan);

        return $this->createInvoice(
            $user,
            'monthly',
            $amount,
            Carbon::now(),
            Carbon::now()->addMonth()
        );
    }

    public function processPayment(Invoice $invoice, string $method, string $reference): void
    {
        DB::transaction(function () use ($invoice, $method, $reference) {
            $invoice->markAsPaid($method, $reference);
            $invoice->user->update(['chatbot_enabled' => true]);
        });
    }

    public function markOverdue(): int
    {
        $overdueInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
        }

        return $overdueInvoices->count();
    }

    public function disableChatbotsForOverdue(): int
    {
        $setting = Setting::get('auto_disable_after_days', 7);
        $cutoffDate = Carbon::now()->subDays($setting);

        $invoices = Invoice::where('status', 'overdue')
            ->where('due_date', '<', $cutoffDate)
            ->whereHas('user', fn ($q) => $q->where('chatbot_enabled', true))
            ->with('user')
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->user->update(['chatbot_enabled' => false]);
        }

        return $invoices->count();
    }

    public function generateMonthlyInvoices(): int
    {
        $clients = User::where('role', 'client')
            ->where('status', 'active')
            ->where('chatbot_enabled', true)
            ->get();

        $count = 0;
        foreach ($clients as $client) {
            $lastInvoice = Invoice::where('user_id', $client->id)
                ->where('type', 'monthly')
                ->whereMonth('billing_period_start', Carbon::now()->month)
                ->whereYear('billing_period_start', Carbon::now()->year)
                ->first();

            if (! $lastInvoice) {
                $this->createMonthlyInvoice($client);
                $count++;
            }
        }

        return $count;
    }

    private function getPlanAmount(string $plan): float
    {
        return match ($plan) {
            'basic' => 1500,
            'standard' => 3000,
            'growth' => 5000,
            'pro' => 10000,
            default => 1500,
        };
    }
}
