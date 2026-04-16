@extends('layouts.admin')
@section('title', 'Conversation Detail')

<div class="mb-4 flex gap-4">
    <a href="{{ route('admin.clients.conversations', $client->id) }}" class="text-gray-400 hover:text-gray-300">← Back to Conversations</a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 mb-6">
    <div class="p-4 border-b border-gray-700">
        <h3 class="text-white font-semibold">Conversation Details</h3>
    </div>
    <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <span class="text-gray-400">Visitor:</span>
            <span class="text-white ml-2">{{ $conversation->visitor_name ?? 'Anonymous' }}</span>
        </div>
        <div>
            <span class="text-gray-400">Email:</span>
            <span class="text-white ml-2">{{ $conversation->visitor_email ?? '-' }}</span>
        </div>
        <div>
            <span class="text-gray-400">Source:</span>
            <span class="text-white ml-2">{{ $conversation->source_url ? \Illuminate\Support\Str::limit($conversation->source_url, 30) : '-' }}</span>
        </div>
        <div>
            <span class="text-gray-400">Date:</span>
            <span class="text-white ml-2">{{ $conversation->created_at->format('M d, Y H:i') }}</span>
        </div>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="p-4 border-b border-gray-700">
        <h3 class="text-white font-semibold">Messages</h3>
    </div>
    <div class="p-4 space-y-4 max-h-[500px] overflow-y-auto">
        @forelse($conversation->messages as $msg)
        <div class="{{ $msg->role === 'visitor' ? 'bg-gray-700 ml-0' : 'bg-indigo-900/50 mr-0 ml-8' }} rounded-lg p-4">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-medium {{ $msg->role === 'visitor' ? 'text-blue-400' : 'text-indigo-400' }}">
                    {{ $msg->role === 'visitor' ? 'Visitor' : 'Bot' }}
                </span>
                <span class="text-xs text-gray-500">{{ $msg->created_at->format('H:i') }}</span>
            </div>
            <p class="text-gray-200 whitespace-pre-wrap">{{ $msg->message }}</p>
            @if($msg->tokens_used > 0)
            <p class="text-xs text-gray-500 mt-2">Tokens used: {{ $msg->tokens_used }}</p>
            @endif
        </div>
        @empty
        <p class="text-gray-500 text-center">No messages</p>
        @endforelse
    </div>
</div>
