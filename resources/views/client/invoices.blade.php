@extends('layouts.client')
@section('title', 'Invoices')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">My Invoices</h1>
</div>

@if($pendingTotal > 0)
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <p class="text-yellow-800">You have <strong>Rs. {{ number_format($pendingTotal, 2) }}</strong> in pending invoices.</p>
</div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ $invoice->invoice_number }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 uppercase">{{ $invoice->type }}</td>
                    <td class="px-6 py-4 text-sm text-green-600 font-medium">Rs. {{ number_format($invoice->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->due_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        @if($invoice->status !== 'paid')
                        <a href="{{ route('client.invoices.pay', $invoice) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">Pay Now →</a>
                        @else
                        <span class="text-gray-400 text-sm">Paid</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No invoices</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t">{{ $invoices->links() }}</div>
</div>
