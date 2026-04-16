<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'message' => 'required|string|max:1000',
            'visitor_id' => 'nullable|string|max:64',
            'conversation_id' => 'nullable|integer',
            'source_url' => 'nullable|string|max:500',
        ]);

        $client = User::where('api_token', $request->token)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid token',
            ], 401);
        }

        $result = $this->chatService->processChat(
            $client,
            $request->message,
            $request->conversation_id,
            $request->visitor_id,
            $request->source_url
        );

        if (! $result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'reply' => $result['reply'],
            'conversation_id' => $result['conversation_id'],
        ]);
    }
}
