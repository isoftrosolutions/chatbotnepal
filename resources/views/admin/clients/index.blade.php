@extends('layouts.admin')
@section('title', 'Clients')
@section('header', 'Bot Manager')

@section('content')
<div class="space-y-6">

  {{-- Stats row — counts from DB, not the paginated page --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
      <div class="w-12 h-12 bg-[#F4F7FE] rounded-xl flex items-center justify-center shrink-0">
        <i data-lucide="users" class="w-5 h-5 text-[#4318FF]"></i>
      </div>
      <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Clients</p>
        <p class="text-2xl font-extrabold text-[#1B1B38]">{{ $clientStats['total'] }}</p>
      </div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
      <div class="w-12 h-12 bg-[#E2FFF3] rounded-xl flex items-center justify-center shrink-0">
        <i data-lucide="circle-check" class="w-5 h-5 text-[#05CD99]"></i>
      </div>
      <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Active</p>
        <p class="text-2xl font-extrabold text-[#1B1B38]">{{ $clientStats['active'] }}</p>
      </div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
      <div class="w-12 h-12 bg-[#FFF5E9] rounded-xl flex items-center justify-center shrink-0">
        <i data-lucide="bot" class="w-5 h-5 text-[#FFB547]"></i>
      </div>
      <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bots On</p>
        <p class="text-2xl font-extrabold text-[#1B1B38]">{{ $clientStats['enabled'] }}</p>
      </div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
      <div class="w-12 h-12 bg-[#FFF5F5] rounded-xl flex items-center justify-center shrink-0">
        <i data-lucide="circle-x" class="w-5 h-5 text-[#EE5D50]"></i>
      </div>
      <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Inactive</p>
        <p class="text-2xl font-extrabold text-[#1B1B38]">{{ $clientStats['inactive'] }}</p>
      </div>
    </div>
  </div>

  {{-- Table card --}}
  <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Header --}}
    <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <h3 class="text-lg font-bold text-[#1B1B38]">All Clients</h3>
      <div class="flex items-center gap-3 w-full sm:w-auto">
        {{-- Search — server-side, works across all pages --}}
        <form method="GET" action="{{ route('admin.clients.index') }}" class="relative flex-1 sm:flex-none" id="client-search-form">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
          <input id="client-search" name="search" type="text"
                 value="{{ request('search') }}"
                 placeholder="Search clients…"
                 class="pl-9 pr-4 py-2.5 text-sm bg-[#F4F7FE] border-none rounded-xl w-full sm:w-56 focus:ring-2 focus:ring-[#4318FF]/20 outline-none">
        </form>
        <a href="{{ route('admin.clients.create') }}"
           class="bg-[#4318FF] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-[0_10px_20px_-5px_rgba(67,24,255,0.3)] hover:scale-[1.02] active:scale-[0.98] transition-all whitespace-nowrap">
          <i data-lucide="plus" class="w-4 h-4"></i>
          Add Client
        </a>
      </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
      <table class="w-full text-left" id="clients-table">
        <thead>
          <tr class="bg-[#F4F7FE]/60">
            <th class="px-6 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Client</th>
            <th class="px-6 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Company</th>
            <th class="px-6 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Plan</th>
            <th class="px-6 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bot</th>
            <th class="px-6 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">KB Files</th>
            <th class="px-6 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50" id="client-rows">
          @forelse($clients as $client)
          @php
            $initials = collect(explode(' ', $client->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
            $colors   = ['bg-[#4318FF]','bg-[#05CD99]','bg-[#FFB547]','bg-[#EE5D50]','bg-purple-500','bg-pink-500'];
            $color    = $colors[$client->id % count($colors)];
            $kbCount  = $client->knowledgeBases()->count();
          @endphp
          <tr class="hover:bg-[#F4F7FE]/40 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 {{ $color }} rounded-xl flex items-center justify-center text-white font-bold text-sm shrink-0">
                  {{ $initials }}
                </div>
                <div>
                  <p class="font-bold text-[#1B1B38] text-sm">{{ $client->name }}</p>
                  <p class="text-xs text-gray-400">{{ $client->email }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 font-medium">
              {{ $client->company_name ?? '—' }}
              @if($client->website_url)
                <a href="{{ $client->website_url }}" target="_blank" class="ml-1 text-gray-300 hover:text-[#4318FF] transition-colors">
                  <i data-lucide="external-link" class="w-3 h-3 inline"></i>
                </a>
              @endif
            </td>
            <td class="px-6 py-4">
              @php
                $planColor = match($client->plan) {
                  'enterprise' => 'bg-[#4318FF]/10 text-[#4318FF]',
                  'growth'     => 'bg-purple-100 text-purple-600',
                  default      => 'bg-[#6AD2FF]/10 text-[#6AD2FF]',
                };
              @endphp
              <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $planColor }}">
                {{ $client->plan }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                {{ $client->status === 'active' ? 'bg-[#05CD99]/10 text-[#05CD99]' : 'bg-[#EE5D50]/10 text-[#EE5D50]' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $client->status === 'active' ? 'bg-[#05CD99]' : 'bg-[#EE5D50]' }}"></span>
                {{ $client->status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <form action="{{ route('admin.clients.toggle', $client->id) }}" method="POST">
                @csrf
                <button type="submit" class="group flex items-center gap-2" title="{{ $client->chatbot_enabled ? 'Disable bot' : 'Enable bot' }}">
                  <div class="w-11 h-6 rounded-full relative transition-colors duration-200 {{ $client->chatbot_enabled ? 'bg-[#05CD99]' : 'bg-gray-200' }}">
                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 {{ $client->chatbot_enabled ? 'translate-x-5' : '' }}"></div>
                  </div>
                </button>
              </form>
            </td>
            <td class="px-6 py-4">
              <a href="{{ route('admin.clients.knowledge-base', $client->id) }}"
                 class="inline-flex items-center gap-1.5 text-sm font-semibold {{ $kbCount > 0 ? 'text-[#4318FF]' : 'text-gray-400' }} hover:underline">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                {{ $kbCount }} file{{ $kbCount !== 1 ? 's' : '' }}
              </a>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-1">
                <a href="{{ route('admin.clients.edit', $client->id) }}"
                   class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Edit">
                  <i data-lucide="edit-3" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('admin.clients.knowledge-base', $client->id) }}"
                   class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Knowledge Base">
                  <i data-lucide="database" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('admin.clients.conversations', $client->id) }}"
                   class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Conversations">
                  <i data-lucide="message-circle" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('admin.clients.usage', $client->id) }}"
                   class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Usage">
                  <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('Delete {{ addslashes($client->name) }}? This cannot be undone.')">
                  @csrf @method('DELETE')
                  <button type="submit" class="p-2 text-gray-400 hover:text-[#EE5D50] hover:bg-[#EE5D50]/5 rounded-lg transition-all" title="Delete">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr id="empty-state">
            <td colspan="7" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3">
                <div class="w-16 h-16 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
                  <i data-lucide="users" class="w-8 h-8 text-gray-300"></i>
                </div>
                <p class="font-bold text-gray-400">No clients yet</p>
                <a href="{{ route('admin.clients.create') }}" class="text-sm text-[#4318FF] font-semibold hover:underline">Add your first client →</a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($clients->hasPages())
    <div class="px-6 py-4 border-t border-gray-50">
      {{ $clients->links() }}
    </div>
    @endif

    <div id="no-results-msg" class="hidden px-6 py-12 text-center">
      <div class="flex flex-col items-center gap-3">
        <div class="w-16 h-16 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
          <i data-lucide="search-x" class="w-8 h-8 text-gray-300"></i>
        </div>
        <p class="font-bold text-gray-400">No clients match your search</p>
      </div>
    </div>
  </div>
</div>

<script>
// Debounced server-side search — works across all pages
let clientSearchTimeout;
document.getElementById('client-search').addEventListener('input', function () {
  clearTimeout(clientSearchTimeout);
  clientSearchTimeout = setTimeout(() => {
    document.getElementById('client-search-form').submit();
  }, 400);
});
</script>
@endsection
