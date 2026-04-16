@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Clients -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
            <i data-lucide="users" class="text-[#4318FF] w-7 h-7"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Total Clients</p>
                <div class="flex items-center gap-1 text-green-500 text-xs font-bold">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    <span>+12%</span>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ $stats['total_clients'] }}</h3>
        </div>
    </div>

    <!-- Conversations Today -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
            <i data-lucide="message-square" class="text-[#05CD99] w-7 h-7"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Conversations Today</p>
                <div class="flex items-center gap-1 text-green-500 text-xs font-bold">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    <span>+5%</span>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ $stats['conversations_today'] }}</h3>
        </div>
    </div>

    <!-- Tokens Used -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#FFF5E9] rounded-2xl flex items-center justify-center">
            <i data-lucide="cpu" class="text-[#FFB547] w-7 h-7"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Tokens Used Today</p>
                <div class="flex items-center gap-1 text-red-500 text-xs font-bold">
                    <i data-lucide="trending-down" class="w-3 h-3"></i>
                    <span>-2%</span>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">{{ number_format($stats['tokens_today']) }}</h3>
        </div>
    </div>

    <!-- Revenue -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 bg-[#E2FFF3] rounded-2xl flex items-center justify-center">
            <i data-lucide="banknote" class="text-[#05CD99] w-7 h-7"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between">
                <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider">Revenue This Month</p>
                <div class="flex items-center gap-1 text-green-500 text-xs font-bold">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    <span>+24%</span>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-[#1B1B38] mt-1">Rs. {{ number_format($stats['revenue_month']) }}</h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Conversations Trend Chart -->
    <div class="lg:col-span-2 bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-lg font-bold text-[#1B1B38]">Conversations Trend</h3>
                <p class="text-sm text-gray-400">Daily message volume across all bots</p>
            </div>
            <div class="flex bg-[#F4F7FE] p-1 rounded-xl">
                <button class="px-4 py-1.5 rounded-lg text-xs font-bold text-[#4318FF] bg-white shadow-sm">7 DAYS</button>
                <button class="px-4 py-1.5 rounded-lg text-xs font-bold text-gray-400 hover:text-gray-600">30 DAYS</button>
            </div>
        </div>
        <div class="h-[300px] w-full">
            <canvas id="conversationsChart"></canvas>
        </div>
    </div>

    <!-- Client Distribution -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-[#1B1B38] mb-8">Client Distribution</h3>
        <div class="relative h-[220px] flex items-center justify-center mb-8">
            <canvas id="distributionChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-3xl font-bold text-[#1B1B38]">{{ $stats['total_clients'] }}</span>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Total</span>
            </div>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#4318FF]"></div>
                    <span class="text-sm font-medium text-gray-500">Enterprise Plan</span>
                </div>
                <span class="text-sm font-bold text-[#1B1B38]">60%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#6AD2FF]"></div>
                    <span class="text-sm font-medium text-gray-500">Growth Plan</span>
                </div>
                <span class="text-sm font-bold text-[#1B1B38]">25%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#05CD99]"></div>
                    <span class="text-sm font-medium text-gray-500">Starter Plan</span>
                </div>
                <span class="text-sm font-bold text-[#1B1B38]">15%</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
        <h3 class="text-lg font-bold text-[#1B1B38]">Recent Activity</h3>
        <a href="#" class="text-sm font-bold text-[#4318FF] hover:underline">View All Logs</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-[#F4F7FE]/50">
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Event</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentConversations as $conversation)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-4 text-sm text-gray-500 font-medium">{{ $conversation->created_at->diffForHumans() }}</td>
                    <td class="px-8 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-[#05CD99]"></div>
                            <span class="text-sm font-bold text-[#1B1B38]">New conversation</span>
                        </div>
                    </td>
                    <td class="px-8 py-4 text-sm font-bold text-[#1B1B38]">{{ $conversation->user->company_name ?? $conversation->user->name }}</td>
                    <td class="px-8 py-4 text-sm text-gray-500">Session #{{ substr($conversation->id, 0, 8) }} started by user via Widget</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-12 text-center text-gray-400 font-medium">No recent activity found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Conversations Trend Chart
        const ctx = document.getElementById('conversationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Conversations',
                    data: [20, 35, 30, 50, 45, 60, 70],
                    borderColor: '#4318FF',
                    borderWidth: 4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4318FF',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                    fill: true,
                    backgroundColor: (context) => {
                        const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, 'rgba(67, 24, 255, 0.1)');
                        gradient.addColorStop(1, 'rgba(67, 24, 255, 0)');
                        return gradient;
                    },
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 12,
                        backgroundColor: '#1B1B38',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 },
                        cornerRadius: 12,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#A3AED0', font: { size: 12, weight: '500' } }
                    },
                    y: {
                        grid: { color: '#F4F7FE', drawBorder: false },
                        border: { display: false },
                        ticks: { display: false }
                    }
                }
            }
        });

        // Client Distribution Chart
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Enterprise', 'Growth', 'Starter'],
                datasets: [{
                    data: [60, 25, 15],
                    backgroundColor: ['#4318FF', '#6AD2FF', '#05CD99'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection
