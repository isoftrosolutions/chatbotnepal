<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(int $clientId, Request $request): View|JsonResponse
    {
        $client = User::where('role', 'client')->findOrFail($clientId);

        $query = ChatConversation::where('user_id', $clientId)
            ->with('messages')
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                  ->orWhere('visitor_email', 'like', "%{$search}%");
            });
        }

        $conversations = $query->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('admin.clients.partials.conversations-table', compact('client', 'conversations'))->render(),
                'pagination' => $conversations->appends($request->query())->links()->toHtml(),
            ]);
        }

        return view('admin.clients.conversations', compact('client', 'conversations'));
    }

    public function show(int $clientId, int $conversationId): View
    {
        $client = User::where('role', 'client')->findOrFail($clientId);

        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $clientId)
            ->with('messages')
            ->firstOrFail();

        return view('admin.clients.conversation-detail', compact('client', 'conversation'));
    }
}
