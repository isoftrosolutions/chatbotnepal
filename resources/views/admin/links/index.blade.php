@extends('layouts.admin')

@section('title', 'My Links')
@section('header', 'My Links')

@section('content')
<div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-[#1B1B38]">Manage Links</h3>
            <p class="text-sm text-gray-500">Add links for WhatsApp, Facebook, booking pages, etc.</p>
        </div>
        <a href="{{ route('admin.links.create') }}" class="bg-[#4318FF] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98] transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New Link
        </a>
    </div>

    @if($links->isEmpty())
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-[#4318FF]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="link" class="w-8 h-8 text-[#4318FF]"></i>
            </div>
            <h4 class="text-lg font-semibold text-[#1B1B38] mb-2">No links yet</h4>
            <p class="text-gray-500 mb-6">Add your first link to use in chatbot responses</p>
            <a href="{{ route('admin.links.create') }}" class="inline-flex items-center gap-2 bg-[#4318FF] text-white px-5 py-2.5 rounded-xl text-sm font-semibold">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Your First Link
            </a>
        </div>
    @else
        <div id="links-list" class="space-y-3">
            @foreach($links as $link)
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors" data-id="{{ $link->id }}">
                    <div class="cursor-move text-gray-400 hover:text-gray-600">
                        <i data-lucide="grip-vertical" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-[#1B1B38]">{{ $link->name }}</span>
                            <span class="text-xs text-gray-400 font-mono">[{{ $link->slug }}]</span>
                            @if($link->is_active)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">Active</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-200 text-gray-500 text-xs font-medium rounded-full">Inactive</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 truncate mt-1" title="{{ $link->link }}">{{ Str::limit($link->link, 50) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="toggle-btn p-2 rounded-xl hover:bg-white transition-colors" data-id="{{ $link->id }}" title="{{ $link->is_active ? 'Disable' : 'Enable' }}">
                            @if($link->is_active)
                                <i data-lucide="eye-off" class="w-5 h-5 text-gray-400 hover:text-gray-600"></i>
                            @else
                                <i data-lucide="eye" class="w-5 h-5 text-gray-400 hover:text-gray-600"></i>
                            @endif
                        </button>
                        <a href="{{ route('admin.links.edit', $link->id) }}" class="p-2 rounded-xl hover:bg-white transition-colors" title="Edit">
                            <i data-lucide="pencil" class="w-5 h-5 text-gray-400 hover:text-gray-600"></i>
                        </a>
                        <form action="{{ route('admin.links.destroy', $link->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl hover:bg-white transition-colors text-red-400 hover:text-red-600" title="Delete" onclick="return confirm('Are you sure you want to delete this link?')">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('links-list');
    if (!list) return;

    new Sortable(list, {
        animation: 150,
        handle: '.cursor-move',
        ghostClass: 'bg-gray-100',
        onEnd: function(evt) {
            const order = [];
            list.querySelectorAll('[data-id]').forEach(el => {
                order.push(el.dataset.id);
            });
            fetch('{{ route("admin.links.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order: order })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    lucide.createIcons();
                }
            });
        }
    });

    // Toggle button handler
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`/admin/links/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });
    });

    lucide.createIcons();
});
</script>
@endsection