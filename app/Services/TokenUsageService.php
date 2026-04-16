<?php

namespace App\Services;

use App\Models\TokenUsageLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TokenUsageService
{
    public function getUserUsage(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $logs = TokenUsageLog::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return [
            'total_tokens' => $logs->sum('total_tokens'),
            'total_api_calls' => $logs->sum('api_calls'),
            'total_cost' => $logs->sum('estimated_cost_npr'),
            'daily_breakdown' => $logs,
        ];
    }

    public function getDailyUsage(User $user, int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);

        return TokenUsageLog::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get()
            ->map(fn ($log) => [
                'date' => $log->date->format('M d'),
                'tokens' => $log->total_tokens,
                'calls' => $log->api_calls,
            ])
            ->toArray();
    }

    public function getAllUsersUsage(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        return User::where('role', 'client')
            ->with(['tokenUsageLogs' => fn ($q) => $q->whereBetween('date', [$startDate, $endDate])])
            ->get()
            ->map(fn ($user) => [
                'user' => $user,
                'total_tokens' => $user->tokenUsageLogs->sum('total_tokens'),
                'total_cost' => $user->tokenUsageLogs->sum('estimated_cost_npr'),
                'total_calls' => $user->tokenUsageLogs->sum('api_calls'),
            ])
            ->toArray();
    }

    public function getTopUsersByUsage(int $limit = 10): array
    {
        return DB::table('token_usage_logs')
            ->join('users', 'token_usage_logs.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.company_name',
                DB::raw('SUM(token_usage_logs.total_tokens) as total_tokens'),
                DB::raw('SUM(token_usage_logs.api_calls) as total_calls'),
                DB::raw('SUM(token_usage_logs.estimated_cost_npr) as total_cost')
            )
            ->where('users.role', 'client')
            ->where('token_usage_logs.date', '>=', Carbon::now()->subDays(30))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.company_name')
            ->orderByDesc('total_tokens')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTotalUsageToday(): array
    {
        $today = Carbon::now()->toDateString();

        $stats = TokenUsageLog::where('date', $today)
            ->selectRaw('SUM(total_tokens) as total_tokens, SUM(api_calls) as total_calls, SUM(estimated_cost_npr) as total_cost')
            ->first();

        return [
            'total_tokens' => $stats->total_tokens ?? 0,
            'total_calls' => $stats->total_calls ?? 0,
            'total_cost' => $stats->total_cost ?? 0,
        ];
    }
}
