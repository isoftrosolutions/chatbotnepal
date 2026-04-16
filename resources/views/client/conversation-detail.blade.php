@extends('layouts.client')
@section('title', 'Conversation')

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Conversation</h1>
        <p class="text-sm text-gray-500">{{ $conversation->created_at->format('M d, Y H:i') }}</p>
    </div>
    <span class="px-3 py-1 rounded-full text-sm {{ $conversation->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
        {{ ucfirst($conversation->status) }}
    </span>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 bg-gray-50 border-b">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Visitor:</span>
                <span class="ml-1 font-medium">{{ $conversation->visitor_name ?? 'Anonymous' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Email:</span>
                <span class="ml-1 font-medium">{{ $conversation->visitor_email ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Source:</span>
                <span class="ml-1 font-medium truncate">{{ $conversation->source_url ? \Illuminate\Support\Str::limit($conversation->source_url, 25) : '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Messages:</span>
                <span class="ml-1 font-medium">{{ $conversation->messages->count() }}</span>
            </div>
        </div>
    </div>
    <div class="p-6 space-y-4 max-h-[500px] overflow-y-auto">
        @forelse($conversation->messages as $msg)
        <div class="{{ $msg->role === 'visitor' ? 'bg-blue-50 ml-0' : 'bg-gray-100 ml-8' }} rounded-lg p-4">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-semibold {{ $msg->role === 'visitor' ? 'text-blue-600' : 'text-indigo-600' }}">
                    {{ $msg->role === 'visitor' ? 'Visitor' : 'Bot' }}
                </span>
                <span class="text-xs text-gray-400">{{ $msg->created_at->format('H:i:s') }}</span>
            </div>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $msg->message }}</p>
            @if($msg->tokens_used > 0)
            <p class="text-xs text-gray-400 mt-2">Tokens: {{ $msg->tokens_used }}</p>
            @endif
        </div>
        @empty
        <p class="text-gray-500 text-center py-8">No messages in this conversation</p>
        @endforelse
    </div>
</div>
