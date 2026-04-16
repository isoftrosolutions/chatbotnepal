@extends('layouts.admin')
@section('title', 'Usage - ' . ($client->company_name ?? $client->name))
@section('header', 'Token Usage: ' . ($client->company_name ?? $client->name))

<div class="mb-4 flex gap-4">
    <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-300">← Back to Clients</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-gray-400 text-sm">Total Tokens (30 days)</div>
        <div class="text-3xl font-bold text-white mt-2">{{ number_format($usage['total_tokens']) }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-gray-400 text-sm">Total API Calls</div>
        <div class="text-3xl font-bold text-white mt-2">{{ number_format($usage['total_api_calls']) }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-gray-400 text-sm">Estimated Cost</div>
        <div class="text-3xl font-bold text-green-400 mt-2">Rs. {{ number_format($usage['total_cost'], 4) }}</div>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="p-4 border-b border-gray-700">
        <h3 class="text-white font-semibold">Daily Breakdown</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Tokens</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">API Calls</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Cost</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($usage['daily_breakdown'] as $log)
                <tr class="hover:bg-gray-700/30">
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $log->date->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-sm text-white">{{ number_format($log->total_tokens) }}</td>
                    <td class="px-4 py-3 text-sm text-white">{{ $log->api_calls }}</td>
                    <td class="px-4 py-3 text-sm text-green-400">Rs. {{ number_format($log->estimated_cost_npr, 4) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">No usage data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
