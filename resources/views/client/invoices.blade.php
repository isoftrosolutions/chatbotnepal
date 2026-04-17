@extends('layouts.client')
@section('title', 'Invoices')
@section('header', 'Billing & Invoices')

@section('content')
<!-- Pending Amount Alert -->
@if($pendingTotal > 0)
<div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-3xl p-6 mb-8">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center">
            <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-amber-800">Payment Due</h3>
            <p class="text-amber-700">You have <span class="font-bold">Rs. {{ number_format($pendingTotal, 2) }}</span> in pending invoices that need attention.</p>
        </div>
        <div class="ml-auto">
            <a href="#pending-invoices" class="px-6 py-3 bg-amber-600 text-white rounded-2xl font-bold hover:bg-amber-700 transition-colors">
                View Pending
            </a>
        </div>
    </div>
</div>
@endif

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6 text-[#05CD99]"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-bold uppercase tracking-wider">Paid</p>
                <h3 class="text-2xl font-bold text-[#1B1B38]">{{ $invoices->where('status', 'paid')->count() }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6 text-[#FFB547]"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-bold uppercase tracking-wider">Pending</p>
                <h3 class="text-2xl font-bold text-[#1B1B38]">{{ $invoices->where('status', 'pending')->count() }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#FEECEC] rounded-2xl flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6 text-[#EE5D50]"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-bold uppercase tracking-wider">Overdue</p>
                <h3 class="text-2xl font-bold text-[#1B1B38]">{{ $invoices->where('status', 'overdue')->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Invoices List -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-50">
        <h3 class="text-lg font-bold text-[#1B1B38]">All Invoices</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-[#F4F7FE]/50">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Invoice #</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Due Date</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50 transition-colors" id="{{ $invoice->status !== 'paid' ? 'pending-invoices' : '' }}">
                    <td class="px-6 py-4">
                        <div class="font-mono text-sm font-bold text-[#1B1B38]">{{ $invoice->invoice_number }}</div>
                        <div class="text-xs text-gray-400">{{ $invoice->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-bold uppercase tracking-wider">
                            {{ $invoice->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-[#05CD99]">Rs. {{ number_format($invoice->amount, 2) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider {{
                            $invoice->status === 'paid' ? 'bg-[#E2FFF3] text-[#05CD99]' :
                            ($invoice->status === 'overdue' ? 'bg-[#FEECEC] text-[#EE5D50]' : 'bg-[#FFF5E9] text-[#FFB547]')
                        }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-[#1B1B38]">{{ $invoice->due_date->format('M d, Y') }}</div>
                        @if($invoice->status === 'overdue')
                            <div class="text-xs text-red-500 font-medium">{{ $invoice->due_date->diffForHumans() }}</div>
                        @elseif($invoice->status === 'pending')
                            <div class="text-xs text-gray-400">Due {{ $invoice->due_date->diffForHumans() }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('client.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-bold flex items-center gap-1">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                View
                            </a>
                            @if($invoice->status !== 'paid')
                            <a href="{{ route('client.invoices.pay', $invoice) }}" class="text-green-600 hover:text-green-700 text-sm font-bold flex items-center gap-1">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                Pay
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="receipt" class="w-8 h-8 text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1B1B38] mb-2">No invoices yet</h3>
                        <p class="text-gray-400">Your billing history will appear here once you have invoices.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
    <div class="p-6 border-t border-gray-50 flex justify-center">
        <div class="bg-gray-50 rounded-2xl p-2">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
});
</script>
