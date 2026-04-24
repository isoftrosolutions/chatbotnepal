@extends('layouts.client')
@section('title', 'Dashboard')
@section('header', 'Overview')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Chatbot Status -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div id="bot-status-icon" class="w-14 h-14 rounded-2xl flex items-center justify-center {{ $stats['chatbot_online'] ? 'bg-[#E2FFF3]' : 'bg-[#FEECEC]' }}">
            <i data-lucide="bot" class="w-7 h-7 {{ $stats['chatbot_online'] ? 'text-[#05CD99]' : 'text-[#EE5D50]' }}"></i>
        </div>
        <div class="flex-1">
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Bot Status</p>
            <div class="flex items-center gap-1.5 mt-1">
                <div id="bot-status-dot" class="w-2 h-2 rounded-full {{ $stats['chatbot_online'] ? 'bg-[#05CD99]' : 'bg-[#EE5D50]' }}"></div>
                <h3 id="bot-status-text" class="text-xl font-bold text-[#1B1B38]">{{ $stats['chatbot_online'] ? 'Online' : 'Offline' }}</h3>
            </div>
        </div>
    </div>

    <!-- Total Conversations -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
            <i data-lucide="messages-square" class="text-[#4318FF] w-7 h-7"></i>
        </div>
        <div class="flex-1">
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Total Chats</p>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1" id="stat-total-conversations">{{ $stats['total_conversations'] }}</h3>
        </div>
    </div>

    <!-- Messages Today -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
            <i data-lucide="message-circle" class="text-[#FFB547] w-7 h-7"></i>
        </div>
        <div class="flex-1">
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Today's Activity</p>
            <div class="flex items-center gap-2">
                <h3 class="text-2xl font-bold text-[#1B1B38] mt-1" id="stat-messages-today">{{ $stats['messages_today'] }}</h3>
                @if(isset($stats['messages_trend']))
                <span id="stat-messages-trend" class="text-xs font-semibold {{ $stats['messages_trend'] >= 0 ? 'text-[#05CD99]' : 'text-[#EE5D50]' }}">
                    {{ $stats['messages_trend'] >= 0 ? '+' : '' }}{{ $stats['messages_trend'] }}%
                </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Plan -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
            <i data-lucide="zap" class="text-[#05CD99] w-7 h-7"></i>
        </div>
        <div class="flex-1">
            <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Current Plan</p>
            <h3 class="text-xl font-bold text-[#1B1B38] mt-1 uppercase">{{ $stats['current_plan'] }}</h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Usage Stats -->
    <div class="lg:col-span-1 space-y-8">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-[#1B1B38] mb-6">Token Consumption</h3>
            <div class="space-y-6">
                <div class="flex items-center justify-between p-4 bg-[#F4F7FE] rounded-2xl">
                    <div class="flex items-center gap-3">
                        <i data-lucide="cpu" class="w-5 h-5 text-[#4318FF]"></i>
                        <span class="text-sm font-medium text-gray-500">Total Tokens</span>
                    </div>
                    <span class="text-lg font-bold text-[#1B1B38]" id="stat-total-tokens">{{ number_format($usage['total_tokens']) }}</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-[#F4F7FE] rounded-2xl">
                    <div class="flex items-center gap-3">
                        <i data-lucide="phone-call" class="w-5 h-5 text-[#05CD99]"></i>
                        <span class="text-sm font-medium text-gray-500">API Calls</span>
                    </div>
                    <span class="text-lg font-bold text-[#1B1B38]" id="stat-api-calls">{{ $usage['total_api_calls'] }}</span>
                </div>
                <div class="mt-4 p-4 border-t border-gray-50">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-400">Est. Cost</span>
                        <span class="text-xl font-black text-[#05CD99]" id="stat-total-cost">Rs. {{ number_format($usage['total_cost'], 2) }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('client.embed-code') }}" class="mt-8 w-full py-4 bg-indigo-600 text-white rounded-2xl text-sm font-bold shadow-lg hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                <i data-lucide="code" class="w-4 h-4"></i>
                Get Embed Code
            </a>
        </div>

        @if($stats['next_billing'])
        <div class="bg-gradient-to-br from-[#1B1B38] to-[#2E2E5D] rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/5 rounded-full blur-3xl"></div>
            <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider mb-2">Next Billing Date</p>
            <h4 class="text-2xl font-bold mb-4">{{ $stats['next_billing']->due_date->format('M d, Y') }}</h4>
            <div class="flex justify-between items-center text-sm font-medium">
                <span class="text-white/60">Amount Due:</span>
                <span class="font-bold">Rs. {{ number_format($stats['next_billing']->amount) }}</span>
            </div>
            <a href="{{ route('client.invoices') }}" class="mt-6 block w-full text-center py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-xs font-bold transition-all">
                View Invoice Details
            </a>
        </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#1B1B38]">Recent Conversations</h3>
            <a href="{{ route('client.conversations') }}" class="text-sm font-bold text-indigo-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-[#F4F7FE]/50">
                        <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Visitor</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Activity</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="recent-conversations-tbody">
                    @forelse($recentConversations as $conv)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-8 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 font-bold text-xs">
                                    {{ substr($conv->visitor_name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-[#1B1B38]">{{ $conv->visitor_name ?? 'Anonymous Visitor' }}</div>
                                    <div class="text-[10px] text-gray-400 font-medium">{{ $conv->visitor_email ?? 'No email provided' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                    {{ $conv->messages->count() }} Messages
                                </span>
                            </div>
                        </td>
                        <td class="px-8 py-4 text-right">
                            <span class="text-sm text-gray-400 font-medium">{{ $conv->created_at->diffForHumans() }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-8 py-12 text-center text-gray-400 font-medium">No conversations recorded yet</td>
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
            // Stat cards
            const el = id => document.getElementById(id);

            el('stat-total-conversations').textContent = data.stats.total_conversations;
            el('stat-messages-today').textContent      = data.stats.messages_today;

            if (el('stat-messages-trend')) {
                const trend = data.stats.messages_trend;
                el('stat-messages-trend').textContent  = (trend >= 0 ? '+' : '') + trend + '%';
                el('stat-messages-trend').className    = 'text-xs font-semibold ' + (trend >= 0 ? 'text-[#05CD99]' : 'text-[#EE5D50]');
            }

            // Bot status
            const online = data.stats.chatbot_online;
            el('bot-status-icon').className = 'w-14 h-14 rounded-2xl flex items-center justify-center ' + (online ? 'bg-[#E2FFF3]' : 'bg-[#FEECEC]');
            el('bot-status-dot').className  = 'w-2 h-2 rounded-full ' + (online ? 'bg-[#05CD99]' : 'bg-[#EE5D50]');
            el('bot-status-text').textContent = online ? 'Online' : 'Offline';

            // Token usage
            el('stat-total-tokens').textContent = data.usage.total_tokens;
            el('stat-api-calls').textContent    = data.usage.total_api_calls;
            el('stat-total-cost').textContent   = 'Rs. ' + data.usage.total_cost;

            // Recent conversations
            const tbody = el('recent-conversations-tbody');
            if (data.recent_conversations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-8 py-12 text-center text-gray-400 font-medium">No conversations recorded yet</td></tr>';
                return;
            }
            tbody.innerHTML = data.recent_conversations.map(c => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 font-bold text-xs">
                                ${c.visitor_initial}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-[#1B1B38]">${c.visitor_name}</div>
                                <div class="text-[10px] text-gray-400 font-medium">${c.visitor_email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                ${c.message_count} Messages
                            </span>
                        </div>
                    </td>
                    <td class="px-8 py-4 text-right">
                        <span class="text-sm text-gray-400 font-medium">${c.time}</span>
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
