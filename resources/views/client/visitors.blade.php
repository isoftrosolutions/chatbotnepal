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
                <p class="text-2xl font-bold text-[#1B1B38]">{{ number_format($totalVisitors) }}</p>
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
                <p class="text-2xl font-bold text-[#1B1B38]">{{ number_format($knownVisitors) }}</p>
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
                <p class="text-2xl font-bold text-[#1B1B38]">{{ number_format($todayVisitors) }}</p>
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
    @forelse($visitors as $visitor)
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 visitor-card">
        <div class="p-5 lg:p-6">
            <!-- Avatar + Name -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-bold text-lg flex-shrink-0
                    {{ $visitor->name ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }}">
                    {{ $visitor->name ? strtoupper(substr($visitor->name, 0, 1)) : '?' }}
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-[#1B1B38] truncate visitor-name">
                        {{ $visitor->name ?? 'Anonymous' }}
                    </h3>
                    <p class="text-xs text-gray-400 truncate visitor-email">
                        {{ $visitor->email ?? 'No email' }}
                    </p>
                </div>
                @if($visitor->name)
                <span class="flex-shrink-0 w-2 h-2 rounded-full bg-emerald-400" title="Identified visitor"></span>
                @endif
            </div>

            <!-- Details -->
            <div class="space-y-2 mb-4">
                @if($visitor->phone)
                <div class="flex items-center gap-2 text-sm text-gray-600 visitor-phone">
                    <i data-lucide="phone" class="w-4 h-4 text-gray-400 flex-shrink-0"></i>
                    <span class="truncate">{{ $visitor->phone }}</span>
                </div>
                @endif
                @if($visitor->last_page_url)
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i data-lucide="globe" class="w-4 h-4 text-gray-400 flex-shrink-0"></i>
                    <span class="truncate text-xs">{{ $visitor->last_page_url }}</span>
                </div>
                @endif
            </div>

            <!-- Stats row -->
            <div class="flex items-center justify-between py-3 border-t border-gray-50 mb-3">
                <div class="text-center">
                    <p class="text-lg font-bold text-[#1B1B38]">{{ $visitor->total_conversations ?? 0 }}</p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Chats</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-[#1B1B38]">{{ $visitor->first_seen_at?->format('M d') ?? '—' }}</p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">First Seen</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-[#1B1B38]">{{ $visitor->last_seen_at?->diffForHumans() ?? '—' }}</p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Last Seen</p>
                </div>
            </div>

            <!-- Conversations link -->
            <a href="{{ route('client.conversations') }}?search={{ urlencode($visitor->email ?? $visitor->name ?? '') }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 rounded-2xl bg-indigo-50 text-indigo-600 text-sm font-semibold hover:bg-indigo-100 transition-colors">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                View Conversations
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-full">
        <div class="bg-white rounded-3xl p-12 shadow-sm border border-gray-100 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="users" class="w-10 h-10 text-gray-300"></i>
            </div>
            <h3 class="text-xl font-bold text-[#1B1B38] mb-2">No visitors yet</h3>
            <p class="text-gray-400 mb-6">Visitors will appear here once they start chatting with your bot.</p>
            <a href="{{ route('client.embed-code') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-colors">
                <i data-lucide="code" class="w-4 h-4"></i>
                Set up your chatbot
            </a>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($visitors->hasPages())
<div class="mt-8 flex justify-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
        {{ $visitors->appends(request()->query())->links() }}
    </div>
</div>
@endif
@endsection

<script>
function sortVisitors(sort) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location = url;
}

document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const searchInput = document.getElementById('visitor-search');
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.visitor-card').forEach(card => {
            const name  = card.querySelector('.visitor-name')?.textContent.toLowerCase() || '';
            const email = card.querySelector('.visitor-email')?.textContent.toLowerCase() || '';
            const phone = card.querySelector('.visitor-phone')?.textContent.toLowerCase() || '';
            card.style.display = (name + email + phone).includes(q) ? 'block' : 'none';
        });
    });

    // Trigger server-side search on Enter
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const url = new URL(window.location);
            if (this.value) url.searchParams.set('search', this.value);
            else url.searchParams.delete('search');
            window.location = url;
        }
    });
});
</script>
