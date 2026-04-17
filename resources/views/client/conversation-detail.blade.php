@extends('layouts.client')
@section('title', 'Conversation')
@section('header', 'Chat Details')

@section('content')
<!-- Conversation Header -->
<div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-indigo-50 rounded-3xl flex items-center justify-center">
                <i data-lucide="user" class="w-8 h-8 text-indigo-600"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-[#1B1B38]">{{ $conversation->visitor_name ?? 'Anonymous Visitor' }}</h1>
                <div class="flex items-center gap-4 mt-1">
                    <span class="text-sm text-gray-500">{{ $conversation->visitor_email ?? 'No email provided' }}</span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider {{ $conversation->status === 'active' ? 'bg-[#E2FFF3] text-[#05CD99]' : 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($conversation->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6 text-sm">
            <div class="text-center">
                <div class="text-2xl font-bold text-[#1B1B38]">{{ $conversation->messages->count() }}</div>
                <div class="text-gray-400 font-medium">Messages</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-[#1B1B38]">
                    @if($conversation->messages->count() > 0)
                        {{ $conversation->created_at->diffForHumans($conversation->messages->last()->created_at ?? $conversation->created_at, true) }}
                    @else
                        0 min
                    @endif
                </div>
                <div class="text-gray-400 font-medium">Duration</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-[#1B1B38]">{{ $conversation->created_at->format('M d, H:i') }}</div>
                <div class="text-gray-400 font-medium">Started</div>
            </div>
        </div>
    </div>

    @if($conversation->source_url)
    <div class="mt-6 pt-6 border-t border-gray-50">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <i data-lucide="link" class="w-4 h-4"></i>
            <span class="font-medium">Source:</span>
            <a href="{{ $conversation->source_url }}" target="_blank" class="text-indigo-600 hover:underline truncate">
                {{ \Illuminate\Support\Str::limit($conversation->source_url, 60) }}
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Chat Messages -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-50">
        <h3 class="text-lg font-bold text-[#1B1B38] flex items-center gap-2">
            <i data-lucide="message-square" class="w-5 h-5 text-indigo-600"></i>
            Conversation Transcript
        </h3>
    </div>

    <div class="max-h-[600px] overflow-y-auto p-6 space-y-6" id="chat-messages">
        @forelse($conversation->messages as $msg)
        <div class="flex {{ $msg->role === 'visitor' ? 'justify-start' : 'justify-end' }} group">
            <div class="max-w-[70%] {{ $msg->role === 'visitor' ? 'order-1' : 'order-2' }}">
                <!-- Message Bubble -->
                <div class="bg-{{ $msg->role === 'visitor' ? 'gray-100' : 'indigo-600' }} rounded-2xl px-4 py-3 shadow-sm relative">
                    <!-- Message Header -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full {{ $msg->role === 'visitor' ? 'bg-gray-200' : 'bg-indigo-500' }} flex items-center justify-center">
                                <i data-lucide="{{ $msg->role === 'visitor' ? 'user' : 'bot' }}" class="w-3 h-3 {{ $msg->role === 'visitor' ? 'text-gray-600' : 'text-white' }}"></i>
                            </div>
                            <span class="text-xs font-bold {{ $msg->role === 'visitor' ? 'text-gray-600' : 'text-indigo-100' }} uppercase tracking-wider">
                                {{ $msg->role === 'visitor' ? 'Visitor' : 'Bot' }}
                            </span>
                        </div>
                        <span class="text-xs {{ $msg->role === 'visitor' ? 'text-gray-400' : 'text-indigo-200' }} font-medium">
                            {{ $msg->created_at->format('H:i') }}
                        </span>
                    </div>

                    <!-- Message Content -->
                    <div class="text-{{ $msg->role === 'visitor' ? 'gray-800' : 'white' }} whitespace-pre-wrap leading-relaxed">
                        {{ $msg->message }}
                    </div>

                    <!-- Token Usage -->
                    @if($msg->tokens_used > 0)
                    <div class="flex items-center gap-1 mt-3 pt-2 border-t {{ $msg->role === 'visitor' ? 'border-gray-200' : 'border-indigo-500' }}">
                        <i data-lucide="zap" class="w-3 h-3 {{ $msg->role === 'visitor' ? 'text-gray-400' : 'text-indigo-200' }}"></i>
                        <span class="text-xs {{ $msg->role === 'visitor' ? 'text-gray-400' : 'text-indigo-200' }} font-medium">
                            {{ $msg->tokens_used }} tokens
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Timestamp (visible on hover) -->
                <div class="text-xs text-gray-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity text-center">
                    {{ $msg->created_at->format('M d, Y H:i:s') }}
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="message-square" class="w-8 h-8 text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-[#1B1B38] mb-2">No messages yet</h3>
            <p class="text-gray-400">This conversation hasn't started yet.</p>
        </div>
        @endforelse
    </div>

    <!-- Chat Actions -->
    <div class="p-6 border-t border-gray-50 bg-gray-50/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="scrollToTop()" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="arrow-up" class="w-4 h-4"></i>
                    Top
                </button>
                <button onclick="scrollToBottom()" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="arrow-down" class="w-4 h-4"></i>
                    Bottom
                </button>
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-400">
                <i data-lucide="info" class="w-4 h-4"></i>
                <span>Total tokens used: <span class="font-bold text-[#1B1B38]">{{ $conversation->messages->sum('tokens_used') }}</span></span>
            </div>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="flex justify-start">
    <a href="{{ route('client.conversations') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to Conversations
    </a>
</div>
@endsection

<script>
function scrollToTop() {
    document.getElementById('chat-messages').scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    container.scrollTo({
        top: container.scrollHeight,
        behavior: 'smooth'
    });
}

// Auto-scroll to bottom on page load
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    scrollToBottom();
});
</script>
