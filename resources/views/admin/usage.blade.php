@extends('layouts.admin')
@section('title', 'Usage Statistics')
@section('header', 'Analytics')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
            <i data-lucide="cpu" class="text-[#4318FF] w-7 h-7"></i>
        </div>
        <div>
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Total Tokens ({{ $days }} days)</p>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ number_format($totalStats['total_tokens']) }}</h3>
        </div>
    </div>
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
            <i data-lucide="phone-call" class="text-[#05CD99] w-7 h-7"></i>
        </div>
        <div>
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Total API Calls</p>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ number_format($totalStats['total_calls']) }}</h3>
        </div>
    </div>
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
            <i data-lucide="banknote" class="text-[#FFB547] w-7 h-7"></i>
        </div>
        <div>
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Total Estimated Cost</p>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">Rs. {{ number_format($totalStats['total_cost'], 2) }}</h3>
        </div>
    </div>
</div>

<!-- Daily Usage Chart & Table -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-[#1B1B38]">Daily Token Usage</h3>
            <p class="text-sm text-gray-400">Consumption overview for the selected period</p>
        </div>
        <select onchange="window.location.href='?days=' + this.value" class="bg-[#F4F7FE] border-none rounded-xl px-4 py-2 text-[#1B1B38] text-sm font-bold focus:ring-2 focus:ring-[#4318FF]/20 transition-all cursor-pointer">
            <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
            <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
            <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
        </select>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-[#F4F7FE]/50">
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tokens</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Calls</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cost</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($dailyData as $day)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-4 text-sm font-medium text-gray-500">{{ $day['date'] }}</td>
                    <td class="px-8 py-4 text-sm font-bold text-[#1B1B38]">{{ number_format($day['tokens']) }}</td>
                    <td class="px-8 py-4 text-sm font-medium text-gray-500">{{ number_format($day['calls']) }}</td>
                    <td class="px-8 py-4 text-sm font-bold text-[#05CD99]">Rs. {{ number_format($day['cost'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Top Users -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-50">
        <h3 class="text-lg font-bold text-[#1B1B38]">Top Users by Usage</h3>
        <p class="text-sm text-gray-400">Clients with the highest token consumption</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-[#F4F7FE]/50">
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Company</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Tokens</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">API Calls</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Cost</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($topUsers as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-4 text-sm font-bold text-[#1B1B38]">{{ $user->name }}</td>
                    <td class="px-8 py-4 text-sm font-medium text-gray-500">{{ $user->company_name ?? '-' }}</td>
                    <td class="px-8 py-4 text-sm font-bold text-[#1B1B38]">{{ number_format($user->total_tokens) }}</td>
                    <td class="px-8 py-4 text-sm font-medium text-gray-500">{{ number_format($user->total_calls) }}</td>
                    <td class="px-8 py-4 text-sm font-bold text-[#05CD99] text-right">Rs. {{ number_format($user->total_cost, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-12 text-center text-gray-400 font-medium">No usage data found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
