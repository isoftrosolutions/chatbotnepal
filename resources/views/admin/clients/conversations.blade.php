@extends('layouts.admin')
@section('title', 'Conversations - ' . ($client->company_name ?? $client->name))
@section('header', 'Conversations')

<div class="mb-4 flex gap-4">
    <a href="{{ route('admin.clients.index') }}" class="text-gray-400 hover:text-gray-300">← Back to Clients</a>
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
            <tbody class="divide-y divide-gray-700">
                @forelse($conversations as $conv)
                <tr class="hover:bg-gray-700/30 cursor-pointer" onclick="window.location='{{ route('admin.clients.conversations.show', [$client->id, $conv->id]) }}'">
                    <td class="px-4 py-3 text-sm text-white">{{ $conv->visitor_name ?? 'Anonymous' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $conv->visitor_email ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400 truncate max-w-xs">{{ $conv->source_url ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $conv->messages->count() }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs {{ $conv->status === 'active' ? 'bg-green-900 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                            {{ ucfirst($conv->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $conv->created_at->format('M d, H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No conversations yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700">{{ $conversations->links() }}</div>
</div>
