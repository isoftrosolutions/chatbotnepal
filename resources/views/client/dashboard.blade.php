@extends('layouts.client')
@section('title', 'Dashboard')
@section('header', 'Overview')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
    <!-- Chatbot Status -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 lg:p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-3 sm:gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div id="bot-status-icon" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center flex-shrink-0 {{ $stats['chatbot_online'] ? 'bg-brand-light' : 'bg-[#fef2f2]' }}">
            <i data-lucide="bot" class="w-5 h-5 {{ $stats['chatbot_online'] ? 'text-brand-deep' : 'text-[#d45656]' }}"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[10px] sm:text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Bot Status</p>
            <div class="flex items-center gap-1.5 mt-1">
                <div id="bot-status-dot" class="w-2 h-2 rounded-full {{ $stats['chatbot_online'] ? 'bg-brand-deep' : 'bg-[#d45656]' }}"></div>
                <h3 id="bot-status-text" class="text-sm sm:text-lg font-semibold text-[#0d0d0d]">{{ $stats['chatbot_online'] ? 'Online' : 'Offline' }}</h3>
            </div>
        </div>
    </div>

    <!-- Total Conversations -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 lg:p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-3 sm:gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#f5f5f5] rounded-full flex items-center justify-center flex-shrink-0">
            <i data-lucide="messages-square" class="text-[#0d0d0d] w-5 h-5"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[10px] sm:text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Total Chats</p>
            <h3 class="text-base sm:text-xl font-semibold text-[#0d0d0d] mt-1 truncate" id="stat-total-conversations">{{ $stats['total_conversations'] }}</h3>
        </div>
    </div>

    <!-- Messages Today -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 lg:p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-3 sm:gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#fef2f2] rounded-full flex items-center justify-center flex-shrink-0">
            <i data-lucide="message-circle" class="text-[#c37d0d] w-5 h-5"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[10px] sm:text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Today's Activity</p>
            <div class="flex items-center gap-2">
                <h3 class="text-base sm:text-xl font-semibold text-[#0d0d0d] mt-1 truncate" id="stat-messages-today">{{ $stats['messages_today'] }}</h3>
                @if(isset($stats['messages_trend']))
                <span id="stat-messages-trend" class="text-[10px] sm:text-xs font-medium whitespace-nowrap {{ $stats['messages_trend'] >= 0 ? 'text-brand-deep' : 'text-[#d45656]' }}">
                    {{ $stats['messages_trend'] >= 0 ? '+' : '' }}{{ $stats['messages_trend'] }}%
                </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Plan -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 lg:p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-3 sm:gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-brand-light rounded-full flex items-center justify-center flex-shrink-0">
            <i data-lucide="zap" class="text-brand-deep w-5 h-5"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[10px] sm:text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Current Plan</p>
            <h3 class="text-sm sm:text-lg font-semibold text-[#0d0d0d] mt-1 truncate uppercase">{{ $stats['current_plan'] }}</h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
    <!-- Usage Stats -->
    <div class="lg:col-span-1 space-y-4 sm:space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-5 lg:p-6 border border-[rgba(0,0,0,0.05)] shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
            <h3 class="text-sm sm:text-base font-semibold text-[#0d0d0d] mb-4 sm:mb-5">Token Consumption</h3>
            <div class="space-y-3 sm:space-y-4">
                <div class="flex items-center justify-between p-2.5 sm:p-3 bg-[#fafafa] rounded-xl">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <i data-lucide="cpu" class="w-4 h-4 text-[#0d0d0d]"></i>
                        <span class="text-xs sm:text-sm text-[#666666]">Total Tokens</span>
                    </div>
                    <span class="text-sm sm:text-base font-semibold text-[#0d0d0d] truncate ml-2" id="stat-total-tokens">{{ number_format($usage['total_tokens']) }}</span>
                </div>
                <div class="flex items-center justify-between p-2.5 sm:p-3 bg-[#fafafa] rounded-xl">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <i data-lucide="phone-call" class="w-4 h-4 text-brand-deep"></i>
                        <span class="text-xs sm:text-sm text-[#666666]">API Calls</span>
                    </div>
                    <span class="text-sm sm:text-base font-semibold text-[#0d0d0d]" id="stat-api-calls">{{ $usage['total_api_calls'] }}</span>
                </div>
                <div class="p-2.5 sm:p-3 border-t border-[rgba(0,0,0,0.05)]">
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm font-medium text-[#888888]">Est. Cost</span>
                        <span class="text-sm sm:text-lg font-semibold text-brand-deep" id="stat-total-cost">Rs. {{ number_format($usage['total_cost'], 2) }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('client.embed-code') }}" class="mt-4 sm:mt-6 w-full py-2.5 sm:py-3 bg-[#0d0d0d] text-white rounded-[9999px] text-sm font-medium flex items-center justify-center gap-2 hover:opacity-90 transition-opacity shadow-[rgba(0,0,0,0.06)_0px_1px_2px]">
                <i data-lucide="code" class="w-4 h-4"></i>
                Get Embed Code
            </a>
        </div>

        @if($stats['next_billing'])
        <div class="bg-[#0d0d0d] rounded-2xl p-4 sm:p-5 lg:p-6 text-white relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-brand/10 rounded-full blur-2xl"></div>
            <p class="text-[10px] text-[#888888] font-medium tracking-[0.65px] uppercase mb-2">Next Billing Date</p>
            <h4 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">{{ $stats['next_billing']->due_date->format('M d, Y') }}</h4>
            <div class="flex justify-between items-center text-xs sm:text-sm">
                <span class="text-[#888888]">Amount Due:</span>
                <span class="font-semibold">Rs. {{ number_format($stats['next_billing']->amount) }}</span>
            </div>
            <a href="{{ route('client.invoices') }}" class="mt-3 sm:mt-4 block w-full text-center py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 border border-white/10 rounded-[9999px] text-xs font-medium transition-colors">
                View Invoice Details
            </a>
        </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-[rgba(0,0,0,0.05)] shadow-[rgba(0,0,0,0.03)_0px_2px_4px] overflow-hidden">
        <div class="p-4 sm:p-5 lg:p-6 border-b border-[rgba(0,0,0,0.05)] flex items-center justify-between flex-wrap gap-2">
            <h3 class="text-sm sm:text-base font-semibold text-[#0d0d0d]">Recent Conversations</h3>
            <a href="{{ route('client.conversations') }}" class="text-xs sm:text-sm font-medium text-brand-deep hover:underline whitespace-nowrap">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[500px]">
                <thead>
                    <tr>
                        <th class="px-3 sm:px-6 py-3 sm:py-4 text-[10px] font-medium text-[#888888] tracking-[0.65px] uppercase">Visitor</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4 text-[10px] font-medium text-[#888888] tracking-[0.65px] uppercase">Activity</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4 text-[10px] font-medium text-[#888888] tracking-[0.65px] uppercase text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[rgba(0,0,0,0.05)]" id="recent-conversations-tbody">
                    @forelse($recentConversations as $conv)
                    <tr class="hover:bg-[#fafafa] transition-colors">
                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="w-8 h-8 sm:w-9 sm:h-9 bg-[#fafafa] rounded-full flex items-center justify-center text-[#0d0d0d] font-semibold text-xs border border-[rgba(0,0,0,0.05)] flex-shrink-0">
                                    {{ substr($conv->visitor_name ?? 'A', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs sm:text-sm font-medium text-[#0d0d0d] truncate">{{ $conv->visitor_name ?? 'Anonymous Visitor' }}</div>
                                    <div class="text-[10px] sm:text-[11px] text-[#888888] truncate hidden sm:block">{{ $conv->visitor_email ?? 'No email provided' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2 sm:px-3 py-1 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full text-[10px] sm:text-[11px] font-medium text-[#666666] whitespace-nowrap">
                                    {{ $conv->messages->count() }} Messages
                                </span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                            <span class="text-xs sm:text-sm text-[#888888] whitespace-nowrap">{{ $conv->created_at->diffForHumans() }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-3 sm:px-6 py-8 sm:py-12 text-center text-[#888888] text-xs sm:text-sm">No conversations recorded yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function pollDashboard() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            const el = id => document.getElementById(id);

            el('stat-total-conversations').textContent = data.stats.total_conversations;
            el('stat-messages-today').textContent      = data.stats.messages_today;

            if (el('stat-messages-trend')) {
                const trend = data.stats.messages_trend;
                el('stat-messages-trend').textContent  = (trend >= 0 ? '+' : '') + trend + '%';
                el('stat-messages-trend').className    = 'text-xs font-medium ' + (trend >= 0 ? 'text-brand-deep' : 'text-[#d45656]');
            }

            const online = data.stats.chatbot_online;
            el('bot-status-icon').className = 'w-12 h-12 rounded-full flex items-center justify-center ' + (online ? 'bg-brand-light' : 'bg-[#fef2f2]');
            el('bot-status-dot').className  = 'w-2 h-2 rounded-full ' + (online ? 'bg-brand-deep' : 'bg-[#d45656]');
            el('bot-status-text').textContent = online ? 'Online' : 'Offline';

            el('stat-total-tokens').textContent = data.usage.total_tokens;
            el('stat-api-calls').textContent    = data.usage.total_api_calls;
            el('stat-total-cost').textContent   = 'Rs. ' + data.usage.total_cost;

            const tbody = el('recent-conversations-tbody');
            if (data.recent_conversations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-3 sm:px-6 py-8 sm:py-12 text-center text-[#888888] text-xs sm:text-sm">No conversations recorded yet</td></tr>';
                return;
            }
            tbody.innerHTML = data.recent_conversations.map(c => `
                <tr class="hover:bg-[#fafafa] transition-colors">
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 bg-[#fafafa] rounded-full flex items-center justify-center text-[#0d0d0d] font-semibold text-xs border border-[rgba(0,0,0,0.05)] flex-shrink-0">
                                ${c.visitor_initial}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs sm:text-sm font-medium text-[#0d0d0d] truncate">${c.visitor_name}</div>
                                <div class="text-[10px] sm:text-[11px] text-[#888888] truncate hidden sm:block">${c.visitor_email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2 sm:px-3 py-1 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full text-[10px] sm:text-[11px] font-medium text-[#666666] whitespace-nowrap">
                                ${c.message_count} Messages
                            </span>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                        <span class="text-xs sm:text-sm text-[#888888] whitespace-nowrap">${c.time}</span>
                    </td>
                </tr>
            `).join('');
        })
        .catch(() => {});
    }

    setInterval(pollDashboard, 30000);
});
</script>
@endsection
