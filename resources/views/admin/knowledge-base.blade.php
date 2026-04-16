@extends('layouts.admin')
@section('title', 'Knowledge Base')
@section('header', 'Knowledge Base')

@section('content')
<div class="max-w-5xl">

    <p class="text-sm text-gray-400 mb-8">
        All client knowledge bases. Click a client to open their KB editor.
        Files are also saved to <code class="bg-white px-2 py-0.5 rounded-lg text-[#4318FF] text-xs font-mono">storage/app/private/clients/</code> on the server.
    </p>

    <div class="space-y-4">
        @forelse($clients as $client)
        @php
            $total  = $client->knowledgeBases->count();
            $active = $client->knowledgeBases->where('is_active', true)->count();
            $chars  = $client->knowledgeBases->where('is_active', true)->sum(fn($f) => strlen($f->content));
        @endphp
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:border-[#4318FF]/30 transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-5">
                    <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
                        <i data-lucide="building-2" class="w-6 h-6 text-[#4318FF]"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-[#1B1B38] text-base">{{ $client->company_name ?? $client->name }}</h4>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $client->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-8">
                    <div class="text-center">
                        <p class="text-lg font-bold text-[#1B1B38]">{{ $total }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Files</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-[#05CD99]">{{ $active }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Active</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-[#1B1B38]">{{ number_format($chars) }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Chars</p>
                    </div>

                    <a href="{{ route('admin.clients.knowledge-base', $client->id) }}"
                       class="bg-[#4318FF] text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-[0_8px_16px_-4px_rgba(67,24,255,0.4)] hover:scale-[1.02] transition-all">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                        Edit KB
                    </a>
                </div>
            </div>

            @if($total > 0)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($client->knowledgeBases as $kb)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                    {{ $kb->is_active ? 'bg-[#E2FFF3] text-[#05CD99]' : 'bg-gray-100 text-gray-400' }}">
                    <i data-lucide="file-text" class="w-3 h-3"></i>
                    {{ $kb->file_name }}
                </span>
                @endforeach
            </div>
            @else
            <p class="mt-4 text-xs text-gray-300 italic">No files yet</p>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-3xl p-16 text-center border border-dashed border-gray-200">
            <i data-lucide="database" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
            <p class="text-gray-400 font-medium">No clients yet.</p>
            <a href="{{ route('admin.clients.create') }}" class="mt-4 inline-block text-[#4318FF] font-bold text-sm">+ Add your first client</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
