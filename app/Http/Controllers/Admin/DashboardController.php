<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Invoice;
use App\Models\TokenUsageLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        // Return chart data only when the toggle buttons request it
        if ($request->ajax() && $request->has('days')) {
            $days = (int) min(max($request->days, 7), 90);
            return response()->json($this->chartData($days));
        }

        $stats = [
            'total_clients'        => User::where('role', 'client')->count(),
            'active_clients'       => User::where('role', 'client')->where('status', 'active')->count(),
            'suspended_clients'    => User::where('role', 'client')->where('status', 'suspended')->count(),
            'conversations_today'  => ChatConversation::whereDate('created_at', Carbon::today())->count(),
            'conversations_month'  => ChatConversation::whereMonth('created_at', Carbon::now()->month)->count(),
            'tokens_today'         => TokenUsageLog::whereDate('date', Carbon::today())->sum('total_tokens'),
            'tokens_month'         => TokenUsageLog::whereMonth('date', Carbon::now()->month)->sum('total_tokens'),
            'revenue_month'        => Invoice::where('status', 'paid')
                ->whereMonth('paid_at', Carbon::now()->month)
                ->sum('amount'),
            'pending_invoices'     => Invoice::where('status', 'pending')->count(),
            'overdue_invoices'     => Invoice::where('status', 'overdue')->count(),
        ];

        $trends = $this->calculateTrends($stats);
        $planDistribution = $this->planDistribution();
        $chartData = $this->chartData(7);

        $recentConversations = ChatConversation::with('user')
            ->latest()
            ->limit(10)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'stats' => [
                    'total_clients'       => $stats['total_clients'],
                    'conversations_today' => $stats['conversations_today'],
                    'tokens_today'        => number_format($stats['tokens_today']),
                    'revenue_month'       => number_format($stats['revenue_month']),
                ],
                'trends' => $trends,
                'recent_conversations' => $recentConversations->map(fn($c) => [
                    'time'       => $c->created_at->diffForHumans(),
                    'client'     => $c->user->company_name ?? $c->user->name,
                    'session_id' => substr($c->id, 0, 8),
                ]),
            ]);
        }

        return view('admin.dashboard', compact(
            'stats', 'trends', 'planDistribution', 'chartData', 'recentConversations'
        ));
    }

    private function calculateTrends(array $stats): array
    {
        $clientsLastMonth = User::where('role', 'client')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $convsYesterday = ChatConversation::whereDate('created_at', Carbon::yesterday())->count();

        $tokensYesterday = TokenUsageLog::whereDate('date', Carbon::yesterday())->sum('total_tokens');

        $revenueLastMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', Carbon::now()->subMonth()->month)
            ->whereYear('paid_at', Carbon::now()->subMonth()->year)
            ->sum('amount');

        return [
            'clients'       => $this->pct($stats['total_clients'], $clientsLastMonth),
            'conversations' => $this->pct($stats['conversations_today'], $convsYesterday),
            'tokens'        => $this->pct($stats['tokens_today'], $tokensYesterday),
            'revenue'       => $this->pct($stats['revenue_month'], $revenueLastMonth),
        ];
    }

    private function pct(int|float $current, int|float $previous): array
    {
        if ($previous > 0) {
            $pct = round((($current - $previous) / $previous) * 100);
        } else {
            $pct = $current > 0 ? 100 : 0;
        }

        return ['value' => $pct, 'up' => $pct >= 0];
    }

    private function planDistribution(): array
    {
        $rows = User::where('role', 'client')
            ->selectRaw('plan, COUNT(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();

        $plans  = ['enterprise', 'growth', 'standard', 'basic', 'starter', 'pro'];
        $colors = ['#4318FF', '#6AD2FF', '#05CD99', '#FFB547', '#EE5D50', '#A855F7'];
        $result = [];

        foreach ($plans as $i => $plan) {
            if (isset($rows[$plan])) {
                $result[] = [
                    'label' => ucfirst($plan),
                    'count' => $rows[$plan],
                    'color' => $colors[$i] ?? '#A3AED0',
                ];
            }
        }

        // Catch any plan names not in the list above
        foreach ($rows as $plan => $count) {
            if (! in_array($plan, $plans)) {
                $result[] = ['label' => ucfirst($plan), 'count' => $count, 'color' => '#A3AED0'];
            }
        }

        return $result;
    }

    private function chartData(int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $counts = ChatConversation::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $data   = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = Carbon::today()->subDays($i);
            $labels[] = $days <= 7 ? $date->format('D') : $date->format('M d');
            $data[]   = $counts[$date->toDateString()] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
