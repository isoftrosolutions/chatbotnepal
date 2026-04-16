<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = $user->conversations()
            ->with('messages')
            ->orderBy('created_at', 'desc');

        if ($request->search) {
            $query->where('visitor_name', 'like', '%'.$request->search.'%')
                ->orWhere('visitor_email', 'like', '%'.$request->search.'%');
        }

        $conversations = $query->paginate(20);

        return view('client.conversations', compact('conversations'));
    }

    public function show(int $id): View
    {
        $user = auth()->user();

        $conversation = ChatConversation::where('id', $id)
            ->where('user_id', $user->id)
            ->with('messages')
            ->firstOrFail();

        return view('client.conversation-detail', compact('conversation'));
    }
}
