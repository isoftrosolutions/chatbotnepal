@extends('layouts.admin')
@section('title', 'Clients')
@section('header', 'Bot Manager')

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
        <h3 class="text-lg font-bold text-[#1B1B38]">Active Bots & Clients</h3>
        <a href="{{ route('admin.clients.create') }}" class="bg-[#4318FF] text-white px-6 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New Client
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-[#F4F7FE]/50">
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Company</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Plan</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Chatbot</th>
                    <th class="px-8 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-4">
                        <div class="font-bold text-[#1B1B38]">{{ $client->name }}</div>
                        <div class="text-xs text-gray-400">{{ $client->email }}</div>
                    </td>
                    <td class="px-8 py-4 text-sm font-medium text-gray-500">{{ $client->company_name ?? '-' }}</td>
                    <td class="px-8 py-4 text-sm">
                        <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $client->plan === 'enterprise' ? 'bg-[#4318FF]/10 text-[#4318FF]' : 'bg-[#6AD2FF]/10 text-[#6AD2FF]' }}">
                            {{ $client->plan }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-sm">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $client->status === 'active' ? 'bg-[#05CD99]/10 text-[#05CD99]' : 'bg-[#EE5D50]/10 text-[#EE5D50]' }}">
                            <div class="w-1.5 h-1.5 rounded-full {{ $client->status === 'active' ? 'bg-[#05CD99]' : 'bg-[#EE5D50]' }}"></div>
                            {{ $client->status }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-sm">
                        <form action="{{ route('admin.clients.toggle', $client->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 group">
                                <div class="w-10 h-5 bg-gray-200 rounded-full relative transition-colors {{ $client->chatbot_enabled ? 'bg-[#05CD99]' : 'bg-gray-200' }}">
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform {{ $client->chatbot_enabled ? 'translate-x-5' : '' }}"></div>
                                </div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase group-hover:text-[#1B1B38]">{{ $client->chatbot_enabled ? 'Enabled' : 'Disabled' }}</span>
                            </button>
                        </form>
                    </td>
                    <td class="px-8 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.clients.edit', $client->id) }}" class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Edit">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('admin.clients.knowledge-base', $client->id) }}" class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Knowledge Base">
                                <i data-lucide="database" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('admin.clients.conversations', $client->id) }}" class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Chats">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this client?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-[#EE5D50] hover:bg-[#EE5D50]/5 rounded-lg transition-all" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">No clients found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())
    <div class="px-8 py-4 border-t border-gray-50">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
