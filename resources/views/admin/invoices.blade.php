@extends('layouts.admin')
@section('title', 'Invoices')
@section('header', 'Knowledge Base')

@section('content')
<!-- Filter Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('admin.invoices.index', ['status' => 'pending']) }}" 
       class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4 hover:scale-[1.02] transition-all {{ request('status') === 'pending' ? 'ring-2 ring-[#FFB547]/50' : '' }}">
        <div class="w-14 h-14 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
            <i data-lucide="clock" class="text-[#FFB547] w-7 h-7"></i>
        </div>
        <div>
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Pending</p>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ $stats['pending'] }}</h3>
        </div>
    </a>

    <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" 
       class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4 hover:scale-[1.02] transition-all {{ request('status') === 'paid' ? 'ring-2 ring-[#05CD99]/50' : '' }}">
        <div class="w-14 h-14 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
            <i data-lucide="check-circle-2" class="text-[#05CD99] w-7 h-7"></i>
        </div>
        <div>
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Paid</p>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ $stats['paid'] }}</h3>
        </div>
    </a>

    <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" 
       class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4 hover:scale-[1.02] transition-all {{ request('status') === 'overdue' ? 'ring-2 ring-[#EE5D50]/50' : '' }}">
        <div class="w-14 h-14 bg-[#FEECEC] rounded-2xl flex items-center justify-center">
            <i data-lucide="alert-triangle" class="text-[#EE5D50] w-7 h-7"></i>
        </div>
        <div>
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Overdue</p>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ $stats['overdue'] }}</h3>
        </div>
    </a>
</div>

<!-- Invoice List -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-[#1B1B38]">Invoices</h3>
            <p class="text-sm text-gray-400">Showing {{ request('status') ?? 'all' }} billing records</p>
        </div>
        <div class="flex gap-2">
            @if(request('status'))
                <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 bg-[#F4F7FE] text-[#4318FF] rounded-xl text-xs font-bold hover:bg-gray-100 transition-colors">Clear Filters</a>
            @endif
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-[#F4F7FE]/50">
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Invoice #</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Due Date</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-4 text-sm font-bold text-[#1B1B38] font-mono">{{ $invoice->invoice_number }}</td>
                    <td class="px-8 py-4">
                        <div class="text-sm font-bold text-[#1B1B38]">{{ $invoice->user->company_name ?? $invoice->user->name }}</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $invoice->type }}</div>
                    </td>
                    <td class="px-8 py-4 text-sm font-bold text-[#1B1B38]">Rs. {{ number_format($invoice->amount, 2) }}</td>
                    <td class="px-8 py-4 text-sm">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider 
                            {{ $invoice->status === 'paid' ? 'bg-[#05CD99]/10 text-[#05CD99]' : ($invoice->status === 'overdue' ? 'bg-[#EE5D50]/10 text-[#EE5D50]' : 'bg-[#FFB547]/10 text-[#FFB547]') }}">
                            <div class="w-1.5 h-1.5 rounded-full 
                                {{ $invoice->status === 'paid' ? 'bg-[#05CD99]' : ($invoice->status === 'overdue' ? 'bg-[#EE5D50]' : 'bg-[#FFB547]') }}"></div>
                            {{ $invoice->status }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-sm font-medium text-gray-500">{{ $invoice->due_date->format('M d, Y') }}</td>
                    <td class="px-8 py-4 text-right">
                        @if($invoice->status !== 'paid')
                        <form action="{{ route('admin.invoices.mark-paid', $invoice->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-[#05CD99]/10 text-[#05CD99] px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-wider hover:bg-[#05CD99] hover:text-white transition-all">
                                Mark Paid
                            </button>
                        </form>
                        @else
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider flex items-center justify-end gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            Settled
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">No billing records found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="px-8 py-4 border-t border-gray-50">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection
