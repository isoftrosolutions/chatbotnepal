<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TokenUsageService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private TokenUsageService $tokenUsageService;

    public function __construct(TokenUsageService $tokenUsageService)
    {
        $this->tokenUsageService = $tokenUsageService;
    }

    public function index(): View
    {
        $user = auth()->user();

        $stats = [
            'chatbot_online' => $user->chatbot_enabled,
            'total_conversations' => $user->conversations()->count(),
            'conversations_this_month' => $user->conversations()
                ->whereMonth('created_at', Carbon::now()->month)
                ->count(),
            'messages_today' => $user->conversations()
                ->whereDate('created_at', Carbon::today())
                ->withCount('messages')
                ->get()
                ->sum('messages_count'),
            'current_plan' => strtoupper($user->plan),
            'next_billing' => $user->invoices()
                ->where('status', 'pending')
                ->orderBy('due_date')
                ->first(),
        ];

        $recentConversations = $user->conversations()
            ->with('messages')
            ->latest()
            ->limit(5)
            ->get();

        $usage = $this->tokenUsageService->getUserUsage($user);

        return view('client.dashboard', compact('stats', 'recentConversations', 'usage'));
    }
}
