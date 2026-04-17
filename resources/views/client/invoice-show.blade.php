@extends('layouts.client')
@section('title', 'Invoice #' . $invoice->invoice_number)
@section('header', 'Invoice Details')

@section('content')
<!-- Invoice Header -->
<div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-indigo-50 rounded-3xl flex items-center justify-center">
                <i data-lucide="receipt" class="w-8 h-8 text-indigo-600"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-[#1B1B38]">Invoice #{{ $invoice->invoice_number }}</h1>
                <div class="flex items-center gap-4 mt-2">
                    <span class="text-sm text-gray-500">Issued {{ $invoice->created_at->format('M d, Y') }}</span>
                    <span class="px-4 py-2 rounded-2xl text-sm font-bold uppercase tracking-wider {{
                        $invoice->status === 'paid' ? 'bg-[#E2FFF3] text-[#05CD99]' :
                        ($invoice->status === 'overdue' ? 'bg-[#FEECEC] text-[#EE5D50]' : 'bg-[#FFF5E9] text-[#FFB547]')
                    }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('client.invoices') }}" class="px-6 py-3 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Invoices
            </a>
            @if($invoice->status !== 'paid')
            <a href="{{ route('client.invoices.pay', $invoice) }}" class="px-6 py-3 bg-green-600 text-white rounded-2xl font-bold hover:bg-green-700 transition-colors flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                Pay Invoice
            </a>
            @endif
        </div>
    </div>
</div>

<!-- Invoice Details -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Invoice Info -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <h3 class="text-xl font-bold text-[#1B1B38] mb-6 flex items-center gap-2">
            <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
            Invoice Information
        </h3>

        <div class="space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-gray-50">
                <span class="text-gray-500 font-medium">Invoice Number</span>
                <span class="font-mono text-[#1B1B38] font-bold">{{ $invoice->invoice_number }}</span>
            </div>

            <div class="flex justify-between items-center py-3 border-b border-gray-50">
                <span class="text-gray-500 font-medium">Type</span>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-bold uppercase tracking-wider">{{ $invoice->type }}</span>
            </div>

            <div class="flex justify-between items-center py-3 border-b border-gray-50">
                <span class="text-gray-500 font-medium">Billing Period</span>
                <span class="text-[#1B1B38] font-medium">
                    {{ $invoice->billing_period_start->format('M d, Y') }} - {{ $invoice->billing_period_end->format('M d, Y') }}
                </span>
            </div>

            <div class="flex justify-between items-center py-3 border-b border-gray-50">
                <span class="text-gray-500 font-medium">Due Date</span>
                <span class="text-[#1B1B38] font-medium {{ $invoice->status === 'overdue' ? 'text-red-500' : '' }}">
                    {{ $invoice->due_date->format('M d, Y') }}
                </span>
            </div>

            @if($invoice->paid_at)
            <div class="flex justify-between items-center py-3 border-b border-gray-50">
                <span class="text-gray-500 font-medium">Paid At</span>
                <span class="text-[#05CD99] font-medium">{{ $invoice->paid_at->format('M d, Y H:i') }}</span>
            </div>
            @endif

            <div class="flex justify-between items-center py-3 pt-6">
                <span class="text-lg font-bold text-[#1B1B38]">Total Amount</span>
                <span class="text-2xl font-black text-[#05CD99]">Rs. {{ number_format($invoice->amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Status -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <h3 class="text-xl font-bold text-[#1B1B38] mb-6 flex items-center gap-2">
            <i data-lucide="credit-card" class="w-5 h-5 text-indigo-600"></i>
            Payment Status
        </h3>

        <div class="text-center py-8">
            @if($invoice->status === 'paid')
            <div class="w-16 h-16 bg-[#E2FFF3] rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check-circle" class="w-8 h-8 text-[#05CD99]"></i>
            </div>
            <h4 class="text-lg font-bold text-[#05CD99] mb-2">Payment Completed</h4>
            <p class="text-gray-500">This invoice has been paid successfully.</p>
            @elseif($invoice->status === 'overdue')
            <div class="w-16 h-16 bg-[#FEECEC] rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="alert-triangle" class="w-8 h-8 text-[#EE5D50]"></i>
            </div>
            <h4 class="text-lg font-bold text-[#EE5D50] mb-2">Payment Overdue</h4>
            <p class="text-gray-500">This invoice is past due. Please pay immediately to avoid service interruption.</p>
            @else
            <div class="w-16 h-16 bg-[#FFF5E9] rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="clock" class="w-8 h-8 text-[#FFB547]"></i>
            </div>
            <h4 class="text-lg font-bold text-[#FFB547] mb-2">Payment Pending</h4>
            <p class="text-gray-500">Please complete payment before the due date.</p>
            @endif
        </div>

        @if($invoice->status !== 'paid')
        <div class="mt-6 pt-6 border-t border-gray-50">
            <div class="text-sm text-gray-500 mb-4">
                Due {{ $invoice->due_date->diffForHumans() }} • Rs. {{ number_format($invoice->amount, 2) }}
            </div>
            <a href="{{ route('client.invoices.pay', $invoice) }}" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                Pay Now
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Payment Methods -->
@if($invoice->status !== 'paid')
<div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
    <h3 class="text-xl font-bold text-[#1B1B38] mb-6 flex items-center gap-2">
        <i data-lucide="wallet" class="w-5 h-5 text-indigo-600"></i>
        Choose Payment Method
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <form action="{{ route('client.invoices.pay', $invoice) }}" method="GET">
            <input type="hidden" name="payment_method" value="esewa">
            <button type="submit" class="w-full p-6 border-2 border-gray-100 rounded-2xl hover:border-green-200 hover:bg-green-50 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <i data-lucide="smartphone" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-[#1B1B38] text-lg">eSewa</div>
                        <div class="text-sm text-gray-500">Pay with eSewa wallet</div>
                    </div>
                    <i data-lucide="arrow-right" class="w-5 h-5 text-gray-300 group-hover:text-green-500 transition-colors ml-auto"></i>
                </div>
            </button>
        </form>

        <form action="{{ route('client.invoices.pay', $invoice) }}" method="GET">
            <input type="hidden" name="payment_method" value="khalti">
            <button type="submit" class="w-full p-6 border-2 border-gray-100 rounded-2xl hover:border-purple-200 hover:bg-purple-50 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <i data-lucide="smartphone" class="w-6 h-6 text-purple-600"></i>
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-[#1B1B38] text-lg">Khalti</div>
                        <div class="text-sm text-gray-500">Pay with Khalti wallet</div>
                    </div>
                    <i data-lucide="arrow-right" class="w-5 h-5 text-gray-300 group-hover:text-purple-500 transition-colors ml-auto"></i>
                </div>
            </button>
        </form>
    </div>
</div>
@endif
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
});
</script>
