@extends('layouts.client')
@section('title', 'Invoice #' . $invoice->invoice_number)

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number }}</h1>
        <p class="text-gray-600">Issued {{ $invoice->created_at->format('M d, Y') }}</p>
    </div>
    <span class="px-4 py-2 rounded-full text-sm font-medium {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
        {{ ucfirst($invoice->status) }}
    </span>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <table class="w-full">
        <tr>
            <td class="py-2 text-gray-500">Billing Period</td>
            <td class="py-2 text-right">{{ $invoice->billing_period_start->format('M d, Y') }} - {{ $invoice->billing_period_end->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td class="py-2 text-gray-500">Type</td>
            <td class="py-2 text-right uppercase">{{ $invoice->type }}</td>
        </tr>
        <tr>
            <td class="py-2 text-gray-500">Due Date</td>
            <td class="py-2 text-right">{{ $invoice->due_date->format('M d, Y') }}</td>
        </tr>
        @if($invoice->paid_at)
        <tr>
            <td class="py-2 text-gray-500">Paid At</td>
            <td class="py-2 text-right">{{ $invoice->paid_at->format('M d, Y H:i') }}</td>
        </tr>
        @endif
        <tr class="border-t">
            <td class="py-4 text-lg font-bold">Total Amount</td>
            <td class="py-4 text-right text-lg font-bold text-green-600">Rs. {{ number_format($invoice->amount, 2) }}</td>
        </tr>
    </table>
</div>

@if($invoice->status !== 'paid')
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold mb-4">Pay with</h3>
    <div class="flex gap-4">
        <form action="{{ route('client.invoices.pay', $invoice) }}" method="GET">
            <input type="hidden" name="payment_method" value="esewa">
            <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                Pay with eSewa
            </button>
        </form>
        <form action="{{ route('client.invoices.pay', $invoice) }}" method="GET">
            <input type="hidden" name="payment_method" value="khalti">
            <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                Pay with Khalti
            </button>
        </form>
    </div>
</div>
@endif
