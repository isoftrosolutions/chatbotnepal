@forelse($conversations as $conv)
<tr class="hover:bg-gray-700/30 cursor-pointer"
    onclick="window.location='{{ route('admin.clients.conversations.show', [$client->id, $conv->id]) }}'">
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
