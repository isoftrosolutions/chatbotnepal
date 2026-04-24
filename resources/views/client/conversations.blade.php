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
                       onkeyup="debounceSearch(this.value)">
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
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6" id="conversations-grid">
    @include('client.partials.conversations-grid')
</div>

<!-- Pagination -->
<div class="mt-8 flex justify-center" id="conversations-pagination">
    @if($conversations->hasPages())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
        {{ $conversations->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

<script>
let searchTimeout;

function fetchConversations(params) {
    const url = new URL(window.location);
    Object.entries(params).forEach(([k, v]) => {
        if (v) url.searchParams.set(k, v);
        else url.searchParams.delete(k);
    });
    history.pushState({}, '', url);

    const grid       = document.getElementById('conversations-grid');
    const pagination = document.getElementById('conversations-pagination');
    grid.style.opacity = '0.5';

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        grid.innerHTML       = data.html;
        pagination.innerHTML = data.pagination;
        grid.style.opacity   = '1';
        lucide.createIcons();
        bindCardClicks();
    })
    .catch(() => { grid.style.opacity = '1'; });
}

function debounceSearch(query) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchConversations({ search: query, page: null }), 400);
}

function filterByStatus(status) {
    fetchConversations({ status: status, page: null });
}

function sortConversations(sort) {
    fetchConversations({ sort: sort, page: null });
}

function bindCardClicks() {
    document.querySelectorAll('.conversation-card').forEach(card => {
        card.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (url) window.location = url;
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    lucide.createIcons();
    bindCardClicks();

    // Handle pagination clicks via AJAX
    document.getElementById('conversations-pagination').addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link) return;
        e.preventDefault();
        const pageUrl = new URL(link.href);
        fetchConversations({ page: pageUrl.searchParams.get('page') });
    });
});
</script>
