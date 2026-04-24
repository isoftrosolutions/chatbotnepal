@extends('layouts.client')
@section('title', 'Visitors')
@section('header', 'Visitors')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6 text-indigo-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Visitors</p>
                <p class="text-2xl font-bold text-[#1B1B38]" id="stat-total-visitors">{{ number_format($totalVisitors) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center">
                <i data-lucide="user-check" class="w-6 h-6 text-emerald-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Identified</p>
                <p class="text-2xl font-bold text-[#1B1B38]" id="stat-known-visitors">{{ number_format($knownVisitors) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-violet-50 rounded-2xl flex items-center justify-center">
                <i data-lucide="activity" class="w-6 h-6 text-violet-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Today</p>
                <p class="text-2xl font-bold text-[#1B1B38]" id="stat-today-visitors">{{ number_format($todayVisitors) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mb-8">
    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text"
                       id="visitor-search"
                       value="{{ request('search') }}"
                       placeholder="Search by name, email, or phone..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
            </div>
        </div>
        <select onchange="sortVisitors(this.value)" class="px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none text-sm bg-white">
            <option value="latest"  @if(request('sort','latest')==='latest')  selected @endif>Latest Activity</option>
            <option value="oldest"  @if(request('sort')==='oldest')  selected @endif>First Seen</option>
            <option value="name"    @if(request('sort')==='name')    selected @endif>Name A-Z</option>
        </select>
    </div>
</div>

<!-- Visitors Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6" id="visitors-grid">
    @include('client.partials.visitors-grid')
</div>

<!-- Pagination -->
<div class="mt-8 flex justify-center" id="visitors-pagination">
    @if($visitors->hasPages())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
        {{ $visitors->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

<script>
let visitorSearchTimeout;

function fetchVisitors(params) {
    const url = new URL(window.location);
    Object.entries(params).forEach(([k, v]) => {
        if (v) url.searchParams.set(k, v);
        else url.searchParams.delete(k);
    });
    history.pushState({}, '', url);

    const grid       = document.getElementById('visitors-grid');
    const pagination = document.getElementById('visitors-pagination');
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

        // Update stat counters
        document.getElementById('stat-total-visitors').textContent = data.totalVisitors;
        document.getElementById('stat-known-visitors').textContent  = data.knownVisitors;
        document.getElementById('stat-today-visitors').textContent  = data.todayVisitors;
    })
    .catch(() => { grid.style.opacity = '1'; });
}

function sortVisitors(sort) {
    fetchVisitors({ sort: sort, page: null });
}

document.addEventListener('DOMContentLoaded', function () {
    lucide.createIcons();

    const searchInput = document.getElementById('visitor-search');
    searchInput.addEventListener('input', function () {
        clearTimeout(visitorSearchTimeout);
        visitorSearchTimeout = setTimeout(() => {
            fetchVisitors({ search: this.value, page: null });
        }, 400);
    });

    // Pagination clicks via AJAX
    document.getElementById('visitors-pagination').addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link) return;
        e.preventDefault();
        const pageUrl = new URL(link.href);
        fetchVisitors({ page: pageUrl.searchParams.get('page') });
    });
});
</script>
