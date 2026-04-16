@extends('layouts.client')
@section('title', 'Conversations')

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-xl font-semibold text-gray-900">Chat History</h2>
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Search</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visitor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Messages</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($conversations as $conv)
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('client.conversations.show', $conv->id) }}'">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $conv->visitor_name ?? 'Anonymous' }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $conv->visitor_email ?? '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $conv->messages->count() }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 rounded-full text-xs {{ $conv->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($conv->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $conv->created_at->format('M d, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No conversations found</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4 border-t">{{ $conversations->links() }}</div>
</div>
