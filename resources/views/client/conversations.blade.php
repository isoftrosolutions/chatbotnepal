@extends('layouts.client')
@section('title', 'Conversations')
@section('header', 'Chat History')

@section('content')
<!-- Search and Filters -->
<div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mb-8">
    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by visitor name or email..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm"
                       onkeyup="filterConversations(this.value)">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <select name="status" onchange="filterByStatus(this.value)" class="px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none text-sm bg-white">
                <option value="">All Status</option>
                <option value="active" @if(request('status') === 'active') selected @endif>Active</option>
                <option value="ended" @if(request('status') === 'ended') selected @endif>Ended</option>
            </select>

            <select name="sort" onchange="sortConversations(this.value)" class="px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none text-sm bg-white">
                <option value="newest" @if(request('sort', 'newest') === 'newest') selected @endif>Newest First</option>
                <option value="oldest" @if(request('sort') === 'oldest') selected @endif>Oldest First</option>
                <option value="most_messages" @if(request('sort') === 'most_messages') selected @endif>Most Messages</option>
            </select>
        </div>
    </div>
</div>

<!-- Conversations Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
    @forelse($conversations as $conv)
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 cursor-pointer group conversation-card transform"
         data-url="{{ route('client.conversations.show', $conv->id) }}">
        <div class="p-4 lg:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-indigo-50 rounded-2xl flex items-center justify-center group-hover:bg-indigo-100 transition-colors flex-shrink-0">
                        <i data-lucide="user" class="w-5 h-5 lg:w-6 lg:h-6 text-indigo-600"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-[#1B1B38] text-base lg:text-lg leading-tight truncate">{{ $conv->visitor_name ?? 'Anonymous Visitor' }}</h3>
                        <p class="text-sm text-gray-400 mt-1 truncate">{{ $conv->visitor_email ?? 'No email provided' }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-end sm:justify-start flex-shrink-0">
                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider {{ $conv->status === 'active' ? 'bg-[#E2FFF3] text-[#05CD99]' : 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($conv->status) }}
                    </span>
                </div>
            </div>

            <!-- Stats -->
            <div class="space-y-3 mb-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 font-medium">Messages</span>
                    <span class="text-lg font-bold text-[#1B1B38]">{{ $conv->messages->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 font-medium">Duration</span>
                    <span class="text-sm font-bold text-[#1B1B38]">
                        @if($conv->messages->count() > 0)
                            {{ $conv->created_at->diffForHumans($conv->messages->last()->created_at ?? $conv->created_at, true) }}
                        @else
                            0 min
                        @endif
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span>{{ $conv->created_at->format('M d, H:i') }}</span>
                </div>
                <div class="flex items-center gap-1 text-indigo-600 group-hover:text-indigo-700 transition-colors">
                    <span class="text-sm font-bold">View Details</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full">
        <div class="bg-white rounded-3xl p-12 shadow-sm border border-gray-100 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="message-square" class="w-10 h-10 text-gray-300"></i>
            </div>
            <h3 class="text-xl font-bold text-[#1B1B38] mb-2">No conversations yet</h3>
            <p class="text-gray-400 mb-6">When visitors start chatting with your bot, they'll appear here.</p>
            <a href="{{ route('client.embed-code') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-colors">
                <i data-lucide="code" class="w-4 h-4"></i>
                Set up your chatbot
            </a>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($conversations->hasPages())
<div class="mt-8 flex justify-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
        {{ $conversations->appends(request()->query())->links() }}
    </div>
</div>
@endif
@endsection

<script>
function filterConversations(query) {
    // Simple client-side filtering for instant feedback
    const cards = document.querySelectorAll('.conversation-card');
    cards.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const email = card.querySelector('p').textContent.toLowerCase();
        const visible = name.includes(query.toLowerCase()) || email.includes(query.toLowerCase());
        card.style.display = visible ? 'block' : 'none';
    });
}

function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
}

function sortConversations(sort) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location = url;
}

// Initialize Lucide icons and card clicks
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    // Handle conversation card clicks
    document.querySelectorAll('.conversation-card').forEach(card => {
        card.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location = url;
            }
        });
    });
});
</script>
