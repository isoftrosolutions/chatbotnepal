<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Invoice;
use App\Models\TokenUsageLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_clients' => User::where('role', 'client')->count(),
            'active_clients' => User::where('role', 'client')->where('status', 'active')->count(),
            'suspended_clients' => User::where('role', 'client')->where('status', 'suspended')->count(),
            'conversations_today' => ChatConversation::whereDate('created_at', Carbon::today())->count(),
            'conversations_month' => ChatConversation::whereMonth('created_at', Carbon::now()->month)->count(),
            'tokens_today' => TokenUsageLog::whereDate('date', Carbon::today())->sum('total_tokens'),
            'tokens_month' => TokenUsageLog::whereMonth('date', Carbon::now()->month)->sum('total_tokens'),
            'revenue_month' => Invoice::where('status', 'paid')
                ->whereMonth('paid_at', Carbon::now()->month)
                ->sum('amount'),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
        ];

        $recentConversations = ChatConversation::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentConversations'));
    }
}
