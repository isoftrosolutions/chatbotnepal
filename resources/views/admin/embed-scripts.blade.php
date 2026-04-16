@extends('layouts.admin')
@section('title', 'Embed Scripts')
@section('header', 'Embed Scripts')

@section('content')
<div class="space-y-6">
    <!-- Info Card -->
    <div class="bg-gradient-to-r from-[#1B1B38] to-[#2D2D5A] rounded-3xl p-6 text-white">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center">
                <i data-lucide="code-2" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold">Client Embed Scripts</h3>
                <p class="text-sm text-gray-300">Copy and share the chatbot embed script for each client</p>
            </div>
        </div>
    </div>

    <!-- Single Client View (when viewing specific client) -->
    @if(isset($client) && isset($embedScript))
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-[#F4F7FE] rounded-xl flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5 text-[#4318FF]"></i>
                </div>
                <div>
                    <h3 class="font-bold text-[#1B1B38]">{{ $client->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $client->email }}</p>
                </div>
            </div>
            <a href="{{ route('admin.embed-scripts') }}" class="text-sm text-[#4318FF] hover:underline flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to all scripts
            </a>
        </div>

        <div class="bg-[#1B1B38] rounded-2xl p-4 relative">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <i data-lucide="code" class="w-16 h-16 text-white"></i>
            </div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Embed Script</span>
                <button onclick="copyToClipboard('{{ $embedScript }}')" class="flex items-center gap-2 text-[10px] font-bold text-indigo-400 uppercase tracking-wider hover:text-white transition-colors">
                    <i data-lucide="copy" class="w-3 h-3"></i>
                    <span id="copy-btn">Copy</span>
                </button>
            </div>
            <code class="block bg-white/5 rounded-xl p-4 font-mono text-sm text-indigo-100 overflow-x-auto whitespace-pre">{{ $embedScript }}</code>
        </div>

        <div class="mt-4 p-4 bg-gray-50 rounded-xl">
            <h4 class="text-sm font-bold text-[#1B1B38] mb-2">Installation Instructions</h4>
            <ol class="text-sm text-gray-500 space-y-1 list-decimal list-inside">
                <li>Copy the script above</li>
                <li>Paste it just before the closing <code class="bg-gray-200 px-1 rounded">&lt;/body&gt;</code> tag</li>
                <li>The chatbot will appear on your client's website</li>
            </ol>
        </div>
    </div>
    @endif

    <!-- All Clients Scripts -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#1B1B38]">All Client Scripts</h3>
            <span class="text-sm text-gray-400">{{ $clients->count() }} clients</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($clients as $client)
            <div class="p-6 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-10 h-10 bg-[#F4F7FE] rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="w-5 h-5 text-[#4318FF]"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-semibold text-[#1B1B38] truncate">{{ $client->name }}</h4>
                            <p class="text-xs text-gray-400 truncate">{{ $client->email }}</p>
                            <p class="text-xs text-gray-300 mt-1">Token: <code class="bg-gray-100 px-1 rounded text-[10px]">{{ Str::limit($client->api_token, 20) }}</code></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <div class="bg-[#1B1B38] rounded-xl px-4 py-2 relative max-w-xs">
                            <code class="text-[10px] text-indigo-200 font-mono truncate block max-w-[200px]">{{ $client->embed_script }}</code>
                        </div>
                        <button onclick="copyToClipboard('{{ addslashes($client->embed_script) }}')" class="w-10 h-10 bg-[#F4F7FE] rounded-xl flex items-center justify-center text-[#4318FF] hover:bg-[#4318FF] hover:text-white transition-all" title="Copy script">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                        <a href="{{ route('admin.embed-scripts.show', $client->id) }}" class="w-10 h-10 bg-[#F4F7FE] rounded-xl flex items-center justify-center text-[#4318FF] hover:bg-[#4318FF] hover:text-white transition-all" title="View details">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="users" class="w-8 h-8 text-gray-300"></i>
                </div>
                <p class="text-gray-400">No active clients found</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-6 right-6 bg-[#1B1B38] text-white px-6 py-3 rounded-xl shadow-xl transform translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-3">
    <i data-lucide="check-circle" class="w-5 h-5 text-[#05CD99]"></i>
    <span id="toast-message">Copied to clipboard!</span>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!');
        });
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        toastMessage.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 2000);
    }
</script>
@endsection
