<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user  = auth()->user();
        $query = $user->conversations()->with('messages');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                  ->orWhere('visitor_email', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('created_at');
        } elseif ($sort === 'most_messages') {
            $query->withCount('messages')->orderByDesc('messages_count');
        } else {
            $query->orderByDesc('created_at');
        }

        $conversations = $query->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('client.partials.conversations-grid', compact('conversations'))->render(),
                'pagination' => $conversations->appends($request->query())->links()->toHtml(),
            ]);
        }

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
