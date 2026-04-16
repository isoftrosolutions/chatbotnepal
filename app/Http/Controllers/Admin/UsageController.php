<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TokenUsageLog;
use App\Models\User;
use App\Services\TokenUsageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsageController extends Controller
{
    private TokenUsageService $tokenUsageService;

    public function __construct(TokenUsageService $tokenUsageService)
    {
        $this->tokenUsageService = $tokenUsageService;
    }

    public function index(Request $request): View
    {
        $days = (int) $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $totalStats = [
            'total_tokens' => 0,
            'total_calls' => 0,
            'total_cost' => 0,
        ];

        $dailyData = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayData = TokenUsageLog::where('date', $date->toDateString())
                ->selectRaw('SUM(total_tokens) as tokens, SUM(api_calls) as calls, SUM(estimated_cost_npr) as cost')
                ->first();

            $dailyData[] = [
                'date' => $date->format('M d'),
                'tokens' => $dayData->tokens ?? 0,
                'calls' => $dayData->calls ?? 0,
                'cost' => $dayData->cost ?? 0,
            ];

            $totalStats['total_tokens'] += $dayData->tokens ?? 0;
            $totalStats['total_calls'] += $dayData->calls ?? 0;
            $totalStats['total_cost'] += $dayData->cost ?? 0;
        }

        $topUsers = $this->tokenUsageService->getTopUsersByUsage(10);

        return view('admin.usage', compact('dailyData', 'totalStats', 'topUsers', 'days'));
    }

    public function clientUsage(int $clientId): View
    {
        $client = User::where('role', 'client')->findOrFail($clientId);
        $usage = $this->tokenUsageService->getUserUsage($client);

        return view('admin.clients.usage', compact('client', 'usage'));
    }
}
