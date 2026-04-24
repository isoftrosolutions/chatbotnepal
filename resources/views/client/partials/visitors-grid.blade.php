@forelse($visitors as $visitor)
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 visitor-card">
    <div class="p-5 lg:p-6">
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
