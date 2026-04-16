@extends('layouts.admin')
@section('title', 'Knowledge Base')
@section('header', 'Bot Manager')

@section('content')
<div class="max-w-6xl">
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.clients.index') }}" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-gray-400 hover:text-[#4318FF] shadow-sm border border-gray-100 transition-all">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h3 class="text-xl font-bold text-[#1B1B38]">{{ $client->company_name ?? $client->name }}</h3>
                <p class="text-sm text-gray-400">Knowledge Base & AI Training Data</p>
                <p class="text-xs text-gray-300 font-mono mt-0.5">storage/app/private/{{ $diskPath }}/</p>
            </div>
        </div>
        
        <button onclick="document.getElementById('add-kb-modal').showModal()" class="bg-[#4318FF] text-white px-6 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New Content
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- KB Files List -->
        <div class="lg:col-span-2 space-y-4">
            @forelse($kbFiles as $file)
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:border-[#4318FF]/30 transition-all group">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#F4F7FE] rounded-2xl flex items-center justify-center text-[#4318FF]">
                            @if($file->file_type === 'faq') <i data-lucide="help-circle" class="w-6 h-6"></i>
                            @elseif($file->file_type === 'services') <i data-lucide="zap" class="w-6 h-6"></i>
                            @else <i data-lucide="file-text" class="w-6 h-6"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-bold text-[#1B1B38]">{{ $file->file_name }}</h4>
                            <span class="text-[10px] font-bold text-[#4318FF] uppercase tracking-wider">{{ $file->file_type }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <form action="{{ route('admin.clients.knowledge-base.toggle', [$client->id, $file->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 {{ $file->is_active ? 'text-[#05CD99] bg-[#05CD99]/10' : 'text-gray-400 bg-gray-50' }} rounded-lg hover:scale-110 transition-all">
                                <i data-lucide="{{ $file->is_active ? 'eye' : 'eye-off' }}" class="w-4 h-4"></i>
                            </button>
                        </form>
                        <button onclick="editKb({{ $file->id }}, '{{ $file->file_name }}', '{{ $file->file_type }}', `{{ addslashes($file->content) }}`)" 
                                class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </button>
                        <form action="{{ route('admin.clients.knowledge-base.destroy', [$client->id, $file->id]) }}" method="POST" onsubmit="return confirm('Delete this content?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-[#EE5D50] hover:bg-[#EE5D50]/5 rounded-lg transition-all">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="mt-4 p-4 bg-[#F4F7FE] rounded-2xl">
                    <pre class="text-xs text-gray-500 font-mono overflow-hidden whitespace-pre-wrap line-clamp-3">{{ $file->content }}</pre>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200">
                <i data-lucide="database" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
                <p class="text-gray-400 font-medium">No knowledge base content added yet.</p>
            </div>
            @endforelse
        </div>

        <!-- AI Preview Card -->
        <div class="lg:col-span-1">
            <div class="bg-[#1B1B38] rounded-3xl p-8 text-white shadow-xl sticky top-8">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="bot" class="w-6 h-6"></i>
                </div>
                <h4 class="text-xl font-bold mb-2">Bot Simulation</h4>
                <p class="text-indigo-300 text-sm mb-6">Test how the AI responds with current KB data.</p>
                
                <div class="space-y-4 mb-8">
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                        <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider mb-2">Active Context Size</p>
                        <p class="text-lg font-bold">{{ number_format($kbFiles->where('is_active', true)->sum(fn($f) => strlen($f->content))) }} chars</p>
                    </div>
                </div>

                <button class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-sm font-bold shadow-lg hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="play" class="w-4 h-4"></i>
                    Launch Preview
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<dialog id="add-kb-modal" class="modal bg-transparent p-0 rounded-3xl overflow-hidden shadow-2xl backdrop:bg-black/50">
    <div class="bg-white w-[600px] max-w-full">
        <form id="kb-form" method="POST" action="{{ route('admin.clients.knowledge-base.store', $client->id) }}">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                <h3 id="modal-title" class="text-xl font-bold text-[#1B1B38]">Add Knowledge Content</h3>
                <button type="button" onclick="document.getElementById('add-kb-modal').close()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">File Name</label>
                        <input type="text" name="file_name" id="file_name" required 
                               class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20" 
                               placeholder="e.g. pricing.md">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Content Type</label>
                        <select name="file_type" id="file_type" required 
                                class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20">
                            <option value="about">About Business</option>
                            <option value="services">Services/Pricing</option>
                            <option value="faq">FAQ</option>
                            <option value="contact">Contact Info</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Markdown Content</label>
                        <label for="md-upload" class="cursor-pointer flex items-center gap-1.5 text-[11px] font-bold text-[#4318FF] hover:text-indigo-700 transition-colors">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            Upload .md file
                        </label>
                        <input type="file" id="md-upload" accept=".md,.markdown,.txt" class="hidden">
                    </div>
                    <textarea name="content" id="content" rows="12" required
                              class="w-full bg-[#F4F7FE] border-none rounded-2xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 font-mono"
                              placeholder="# Heading...&#10;&#10;Type your content here, or upload a .md file above."></textarea>
                </div>
            </div>
            <div class="p-8 bg-gray-50 flex justify-end gap-4">
                <button type="button" onclick="document.getElementById('add-kb-modal').close()" class="px-6 py-2 text-sm font-bold text-gray-400">Cancel</button>
                <button type="submit" class="bg-[#4318FF] text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg hover:scale-105 transition-all">Save Content</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    function editKb(id, name, type, content) {
        const modal = document.getElementById('add-kb-modal');
        const form = document.getElementById('kb-form');
        const title = document.getElementById('modal-title');

        form.action = `/admin/clients/{{ $client->id }}/knowledge-base/${id}`;
        document.getElementById('form-method').value = 'PUT';
        title.innerText = 'Edit Knowledge Content';

        document.getElementById('file_name').value = name;
        document.getElementById('file_type').value = type;
        document.getElementById('content').value = content;

        modal.showModal();
    }

    // Reset form when opening for new content
    document.querySelector('[onclick="document.getElementById(\'add-kb-modal\').showModal()"]')
        .addEventListener('click', function () {
            const form = document.getElementById('kb-form');
            form.action = '{{ route("admin.clients.knowledge-base.store", $client->id) }}';
            document.getElementById('form-method').value = 'POST';
            document.getElementById('modal-title').innerText = 'Add Knowledge Content';
            document.getElementById('file_name').value = '';
            document.getElementById('file_type').value = 'about';
            document.getElementById('content').value = '';
        });

    // Upload .md file → read content into textarea
    document.getElementById('md-upload').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('content').value = ev.target.result;

            // Auto-fill file name if empty
            const nameField = document.getElementById('file_name');
            if (!nameField.value) {
                nameField.value = file.name.replace(/\.[^.]+$/, '') + '.md';
            }
        };
        reader.readAsText(file);
        this.value = ''; // reset so same file can be re-uploaded
    });
</script>
@endsection
