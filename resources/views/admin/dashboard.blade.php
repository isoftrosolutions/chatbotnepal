@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Clients -->
    <div class="bg-white rounded-2xl p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="w-12 h-12 bg-[#f5f5f5] rounded-full flex items-center justify-center">
            <i data-lucide="users" class="text-[#0d0d0d] w-5 h-5"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Total Clients</p>
                <div id="trend-clients" class="flex items-center gap-1 text-xs font-medium {{ $trends['clients']['up'] ? 'text-brand-deep' : 'text-[#d45656]' }}">
                    <i data-lucide="{{ $trends['clients']['up'] ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                    <span>{{ ($trends['clients']['up'] ? '+' : '') }}{{ $trends['clients']['value'] }}%</span>
                </div>
            </div>
            <h3 class="text-xl font-semibold text-[#0d0d0d] mt-1" id="stat-total-clients">{{ $stats['total_clients'] }}</h3>
        </div>
    </div>

    <!-- Conversations Today -->
    <div class="bg-white rounded-2xl p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="w-12 h-12 bg-brand-light rounded-full flex items-center justify-center">
            <i data-lucide="message-square" class="text-brand-deep w-5 h-5"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Conversations Today</p>
                <div id="trend-conversations" class="flex items-center gap-1 text-xs font-medium {{ $trends['conversations']['up'] ? 'text-brand-deep' : 'text-[#d45656]' }}">
                    <i data-lucide="{{ $trends['conversations']['up'] ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                    <span>{{ ($trends['conversations']['up'] ? '+' : '') }}{{ $trends['conversations']['value'] }}%</span>
                </div>
            </div>
            <h3 class="text-xl font-semibold text-[#0d0d0d] mt-1" id="stat-conversations-today">{{ $stats['conversations_today'] }}</h3>
        </div>
    </div>

    <!-- Tokens Used -->
    <div class="bg-white rounded-2xl p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="w-12 h-12 bg-[#fef2f2] rounded-full flex items-center justify-center">
            <i data-lucide="cpu" class="text-[#c37d0d] w-5 h-5"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Tokens Used Today</p>
                <div id="trend-tokens" class="flex items-center gap-1 text-xs font-medium {{ $trends['tokens']['up'] ? 'text-brand-deep' : 'text-[#d45656]' }}">
                    <i data-lucide="{{ $trends['tokens']['up'] ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                    <span>{{ ($trends['tokens']['up'] ? '+' : '') }}{{ $trends['tokens']['value'] }}%</span>
                </div>
            </div>
            <h3 class="text-xl font-semibold text-[#0d0d0d] mt-1" id="stat-tokens-today">{{ number_format($stats['tokens_today']) }}</h3>
        </div>
    </div>

    <!-- Revenue -->
    <div class="bg-white rounded-2xl p-6 border border-[rgba(0,0,0,0.05)] flex items-center gap-4 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="w-12 h-12 bg-brand-light rounded-full flex items-center justify-center">
            <i data-lucide="banknote" class="text-brand-deep w-5 h-5"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-[#888888] font-medium tracking-[0.65px] uppercase">Revenue This Month</p>
                <div id="trend-revenue" class="flex items-center gap-1 text-xs font-medium {{ $trends['revenue']['up'] ? 'text-brand-deep' : 'text-[#d45656]' }}">
                    <i data-lucide="{{ $trends['revenue']['up'] ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                    <span>{{ ($trends['revenue']['up'] ? '+' : '') }}{{ $trends['revenue']['value'] }}%</span>
                </div>
            </div>
            <h3 class="text-xl font-semibold text-[#0d0d0d] mt-1" id="stat-revenue-month">Rs. {{ number_format($stats['revenue_month']) }}</h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Conversations Trend Chart -->
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-[rgba(0,0,0,0.05)] shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-semibold text-[#0d0d0d]">Conversations Trend</h3>
                <p class="text-sm text-[#888888]">Daily conversation volume across all bots</p>
            </div>
            <div class="flex bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full p-1" id="chart-toggle">
                <button data-days="7"  class="chart-toggle-btn px-4 py-1.5 rounded-full text-xs font-medium text-[#0d0d0d] bg-white border border-[rgba(0,0,0,0.05)]">7 DAYS</button>
                <button data-days="30" class="chart-toggle-btn px-4 py-1.5 rounded-full text-xs font-medium text-[#888888] hover:text-[#0d0d0d]">30 DAYS</button>
            </div>
        </div>
        <div class="h-[300px] w-full">
            <canvas id="conversationsChart"></canvas>
        </div>
    </div>

    <!-- Client Distribution -->
    <div class="bg-white rounded-2xl p-6 border border-[rgba(0,0,0,0.05)] shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
        <h3 class="text-base font-semibold text-[#0d0d0d] mb-6">Client Distribution</h3>
        <div class="relative h-[220px] flex items-center justify-center mb-6">
            <canvas id="distributionChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-3xl font-semibold text-[#0d0d0d]">{{ $stats['total_clients'] }}</span>
                <span class="text-[10px] text-[#888888] font-medium tracking-[0.65px] uppercase">Total</span>
            </div>
        </div>
        <div class="space-y-3" id="plan-legend">
            @forelse($planDistribution as $plan)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $plan['color'] }}"></div>
                    <span class="text-sm text-[#666666]">{{ $plan['label'] }} Plan</span>
                </div>
                <span class="text-sm font-medium text-[#0d0d0d]">
                    {{ $stats['total_clients'] > 0 ? round(($plan['count'] / $stats['total_clients']) * 100) : 0 }}%
                </span>
            </div>
            @empty
            <p class="text-sm text-[#888888] text-center">No clients yet</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-2xl border border-[rgba(0,0,0,0.05)] shadow-[rgba(0,0,0,0.03)_0px_2px_4px] overflow-hidden">
    <div class="p-6 border-b border-[rgba(0,0,0,0.05)] flex items-center justify-between">
        <h3 class="text-base font-semibold text-[#0d0d0d]">Recent Activity</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-[10px] font-medium text-[#888888] tracking-[0.65px] uppercase">Time</th>
                    <th class="px-6 py-4 text-[10px] font-medium text-[#888888] tracking-[0.65px] uppercase">Event</th>
                    <th class="px-6 py-4 text-[10px] font-medium text-[#888888] tracking-[0.65px] uppercase">Client</th>
                    <th class="px-6 py-4 text-[10px] font-medium text-[#888888] tracking-[0.65px] uppercase">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[rgba(0,0,0,0.05)]" id="recent-activity-tbody">
                @forelse($recentConversations as $conversation)
                <tr class="hover:bg-[#fafafa] transition-colors">
                    <td class="px-6 py-4 text-sm text-[#666666]">{{ $conversation->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-brand-deep"></div>
                            <span class="text-sm font-medium text-[#0d0d0d]">New conversation</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-[#0d0d0d]">{{ $conversation->user->company_name ?? $conversation->user->name }}</td>
                    <td class="px-6 py-4 text-sm text-[#666666]">Session #{{ substr($conversation->id, 0, 8) }} started by user via Widget</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-[#888888] text-sm">No recent activity found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index', intersect: false, padding: 12,
                backgroundColor: '#0d0d0d',
                titleFont: { size: 12, weight: '600' },
                bodyFont: { size: 12 },
                cornerRadius: 9999, displayColors: false
            }
        },
        scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { color: '#888888', font: { size: 12, weight: '500' } } },
            y: { grid: { color: 'rgba(0,0,0,0.03)' }, border: { display: false }, ticks: { display: false } }
        }
    };

    function makeGradient(ctx) {
        const g = ctx.createLinearGradient(0, 0, 0, 400);
        g.addColorStop(0, 'rgba(24, 226, 153, 0.12)');
        g.addColorStop(1, 'rgba(24, 226, 153, 0)');
        return g;
    }

    const lineChart = new Chart(document.getElementById('conversationsChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Conversations',
                data: @json($chartData['data']),
                borderColor: '#18E299', borderWidth: 3,
                pointRadius: 0, pointHoverRadius: 6,
                pointHoverBackgroundColor: '#18E299',
                pointHoverBorderColor: '#fff', pointHoverBorderWidth: 3,
                fill: true,
                backgroundColor: (ctx) => makeGradient(ctx.chart.ctx),
                tension: 0.4
            }]
        },
        options: chartOptions
    });

    document.querySelectorAll('.chart-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.chart-toggle-btn').forEach(b => {
                b.className = 'chart-toggle-btn px-4 py-1.5 rounded-full text-xs font-medium text-[#888888] hover:text-[#0d0d0d]';
            });
            this.className = 'chart-toggle-btn px-4 py-1.5 rounded-full text-xs font-medium text-[#0d0d0d] bg-white border border-[rgba(0,0,0,0.05)]';

            fetch(window.location.href + '?days=' + this.dataset.days, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(d => {
                lineChart.data.labels   = d.labels;
                lineChart.data.datasets[0].data = d.data;
                lineChart.update();
            })
            .catch(() => {});
        });
    });

    const planData  = @json($planDistribution);
    const distChart = new Chart(document.getElementById('distributionChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: planData.map(p => p.label),
            datasets: [{
                data: planData.length ? planData.map(p => p.count) : [1],
                backgroundColor: planData.length ? planData.map(p => p.color) : ['#e5e5e5'],
                borderWidth: 0, hoverOffset: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { display: false } } }
    });

    function renderTrend(el, trend) {
        const up   = trend.up;
        const icon = up ? 'trending-up' : 'trending-down';
        const cls  = up ? 'text-brand-deep' : 'text-[#d45656]';
        el.className = `flex items-center gap-1 text-xs font-medium ${cls}`;
        el.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${up
                ? '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'
                : '<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>'}
        </svg><span>${up ? '+' : ''}${trend.value}%</span>`;
    }

    function pollDashboard() {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-total-clients').textContent       = data.stats.total_clients;
            document.getElementById('stat-conversations-today').textContent = data.stats.conversations_today;
            document.getElementById('stat-tokens-today').textContent        = data.stats.tokens_today;
            document.getElementById('stat-revenue-month').textContent       = 'Rs. ' + data.stats.revenue_month;

            if (data.trends) {
                renderTrend(document.getElementById('trend-clients'),       data.trends.clients);
                renderTrend(document.getElementById('trend-conversations'), data.trends.conversations);
                renderTrend(document.getElementById('trend-tokens'),        data.trends.tokens);
                renderTrend(document.getElementById('trend-revenue'),       data.trends.revenue);
            }

            const activeDays = document.querySelector('.chart-toggle-btn.bg-white')?.dataset.days ?? 7;
            fetch(window.location.href + '?days=' + activeDays, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(d => {
                if (d.labels && d.data) {
                    lineChart.data.labels              = d.labels;
                    lineChart.data.datasets[0].data    = d.data;
                    lineChart.update('none');
                }
            })
            .catch(() => {});

            const tbody = document.getElementById('recent-activity-tbody');
            if (!data.recent_conversations.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-[#888888] text-sm">No recent activity found</td></tr>';
                return;
            }
            tbody.innerHTML = data.recent_conversations.map(c => `
                <tr class="hover:bg-[#fafafa] transition-colors">
                    <td class="px-6 py-4 text-sm text-[#666666]">${c.time}</td>
                    <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-brand-deep"></div><span class="text-sm font-medium text-[#0d0d0d]">New conversation</span></div></td>
                    <td class="px-6 py-4 text-sm font-medium text-[#0d0d0d]">${c.client}</td>
                    <td class="px-6 py-4 text-sm text-[#666666]">Session #${c.session_id} started by user via Widget</td>
                </tr>
            `).join('');
        })
        .catch(() => {});
    }

    setInterval(pollDashboard, 30000);
});
</script>
@endsection
