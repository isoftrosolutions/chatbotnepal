@extends('layouts.admin')
@section('title', 'Conversations - ' . ($client->company_name ?? $client->name))
@section('header', 'Conversations')

@section('content')
<div class="mb-4 flex items-center justify-between gap-4">
    <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-300 text-sm">← Back to Clients</a>
    <div class="flex items-center gap-3">
        <!-- Search -->
        <div class="relative">
            <input type="text"
                   id="admin-conv-search"
                   value="{{ request('search') }}"
                   placeholder="Search visitor..."
                   class="pl-9 pr-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500">
            <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <!-- Status filter -->
        <select id="admin-conv-status" onchange="filterAdminConversations()"
                class="px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
            <option value="">All Status</option>
            <option value="active"  @if(request('status') === 'active')  selected @endif>Active</option>
            <option value="ended"   @if(request('status') === 'ended')   selected @endif>Ended</option>
        </select>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Visitor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Source</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Messages</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700" id="admin-conversations-tbody">
                @include('admin.clients.partials.conversations-table')
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700" id="admin-conversations-pagination">
        {{ $conversations->links() }}
    </div>
</div>
@endsection

<script>
let adminSearchTimeout;

function fetchAdminConversations(params) {
    const url = new URL(window.location);
    Object.entries(params).forEach(([k, v]) => {
        if (v) url.searchParams.set(k, v);
        else url.searchParams.delete(k);
    });
    history.pushState({}, '', url);

    const tbody      = document.getElementById('admin-conversations-tbody');
    const pagination = document.getElementById('admin-conversations-pagination');
    tbody.style.opacity = '0.5';

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        tbody.innerHTML      = data.html;
        pagination.innerHTML = data.pagination;
        tbody.style.opacity  = '1';
    })
    .catch(() => { tbody.style.opacity = '1'; });
}

function filterAdminConversations() {
    const status = document.getElementById('admin-conv-status').value;
    const search = document.getElementById('admin-conv-search').value;
    fetchAdminConversations({ status: status, search: search, page: null });
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('admin-conv-search').addEventListener('input', function () {
        clearTimeout(adminSearchTimeout);
        adminSearchTimeout = setTimeout(() => filterAdminConversations(), 400);
    });

    document.getElementById('admin-conversations-pagination').addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link) return;
        e.preventDefault();
        const pageUrl = new URL(link.href);
        fetchAdminConversations({ page: pageUrl.searchParams.get('page') });
    });
});
</script>
