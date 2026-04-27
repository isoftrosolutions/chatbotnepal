<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TokenUsageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private TokenUsageService $tokenUsageService;

    public function __construct(TokenUsageService $tokenUsageService)
    {
        $this->tokenUsageService = $tokenUsageService;
    }

    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();

        $messagesToday = $user->conversations()
            ->whereDate('created_at', Carbon::today())
            ->withCount('messages')
            ->get()
            ->sum('messages_count');

        $messagesYesterday = $user->conversations()
            ->whereDate('created_at', Carbon::yesterday())
            ->withCount('messages')
            ->get()
            ->sum('messages_count');

        $todayTrend = $messagesYesterday > 0
            ? round((($messagesToday - $messagesYesterday) / $messagesYesterday) * 100)
            : ($messagesToday > 0 ? 100 : 0);

        $stats = [
            'chatbot_online' => $user->chatbot_enabled,
            'total_conversations' => $user->conversations()->count(),
            'conversations_this_month' => $user->conversations()
                ->whereMonth('created_at', Carbon::now()->month)
                ->count(),
            'messages_today' => $messagesToday,
            'messages_trend' => $todayTrend,
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

        /* Client-friendly summary — no internal cost/token data */
        $clientSummary = [
            'conversations_this_month' => $stats['conversations_this_month'],
            'bot_responses_this_month' => $usage['total_api_calls'],
        ];

        if ($request->ajax()) {
            return response()->json([
                'stats' => [
                    'chatbot_online'           => $stats['chatbot_online'],
                    'total_conversations'      => $stats['total_conversations'],
                    'conversations_this_month' => $stats['conversations_this_month'],
                    'messages_today'           => $stats['messages_today'],
                    'messages_trend'           => $stats['messages_trend'],
                ],
                'usage' => [
                    'conversations_this_month' => $clientSummary['conversations_this_month'],
                    'bot_responses_this_month' => $clientSummary['bot_responses_this_month'],
                ],
                'recent_conversations' => $recentConversations->map(fn($conv) => [
                    'visitor_initial' => strtoupper(substr($conv->visitor_name ?? 'A', 0, 1)),
                    'visitor_name'    => $conv->visitor_name ?? 'Guest Visitor',
                    'visitor_email'   => $conv->visitor_email ?? '',
                    'message_count'   => $conv->messages->count(),
                    'time'            => $conv->created_at->diffForHumans(),
                ]),
            ]);
        }

        return view('client.dashboard', compact('stats', 'recentConversations', 'usage', 'clientSummary'));
    }
}
