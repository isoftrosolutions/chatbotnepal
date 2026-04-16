<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(int $clientId, Request $request): View
    {
        $client = User::where('role', 'client')->findOrFail($clientId);

        $query = ChatConversation::where('user_id', $clientId)
            ->with('messages')
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $conversations = $query->paginate(20);

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
