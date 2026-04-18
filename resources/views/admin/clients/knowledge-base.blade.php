@extends('layouts.admin')
@section('title', 'Knowledge Base')
@section('header', 'Bot Manager')

@section('content')
<div class="max-w-6xl">

  {{-- Top bar --}}
  <div class="mb-6 flex items-center justify-between gap-4">
    <div class="flex items-center gap-4">
      <a href="{{ route('admin.clients.index') }}"
         class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-gray-400 hover:text-[#4318FF] shadow-sm border border-gray-100 transition-all">
        <i data-lucide="chevron-left" class="w-5 h-5"></i>
      </a>
      <div>
        <h3 class="text-xl font-bold text-[#1B1B38]">{{ $client->company_name ?? $client->name }}</h3>
        <p class="text-sm text-gray-400">Knowledge Base &amp; AI Training Data</p>
      </div>
    </div>
    <button onclick="openAddModal()"
      class="bg-[#4318FF] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-[0_10px_20px_-5px_rgba(67,24,255,0.35)] hover:scale-[1.02] active:scale-[0.98] transition-all">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Add Content
    </button>
  </div>

  @if(session('success'))
  <div class="mb-4 bg-[#E2FFF3] border border-[#05CD99]/20 text-[#05CD99] rounded-xl px-5 py-3 text-sm font-semibold flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    {{ session('success') }}
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: KB file list ── --}}
    <div class="lg:col-span-2 space-y-4">

      {{-- Filter tabs --}}
      <div class="flex items-center gap-2 flex-wrap">
        <button class="kb-tab active" data-type="all">All
          <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-[#4318FF]/10 text-[#4318FF]">{{ $kbFiles->count() }}</span>
        </button>
        @foreach(['about','services','faq','contact','custom'] as $t)
        @php $cnt = $kbFiles->where('file_type', $t)->count(); @endphp
        @if($cnt)
        <button class="kb-tab" data-type="{{ $t }}">{{ ucfirst($t) }}
          <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-gray-100 text-gray-500">{{ $cnt }}</span>
        </button>
        @endif
        @endforeach
      </div>

      {{-- Drag-drop zone for file upload --}}
      <div id="drop-zone"
           class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center transition-all cursor-pointer hover:border-[#4318FF]/40 hover:bg-[#4318FF]/[0.02]">
        <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
        <p class="text-sm font-semibold text-gray-400">Drop <span class="text-[#4318FF]">.md</span> or <span class="text-[#4318FF]">.txt</span> files here to add</p>
        <p class="text-xs text-gray-300 mt-1">or <label for="dz-file-input" class="text-[#4318FF] cursor-pointer underline">browse files</label></p>
        <input type="file" id="dz-file-input" accept=".md,.markdown,.txt" multiple class="hidden">
      </div>

      {{-- Sortable list --}}
      @if($kbFiles->isNotEmpty())
      <div class="text-xs text-gray-400 flex items-center gap-1.5 px-1">
        <i data-lucide="grip-vertical" class="w-3.5 h-3.5"></i>
        Drag rows to reorder priority
      </div>
      @endif

      <div id="kb-list" class="space-y-3">
        @forelse($kbFiles as $file)
        @php
          $typeIcon = match($file->file_type) {
            'faq'      => 'help-circle',
            'services' => 'zap',
            'about'    => 'building-2',
            'contact'  => 'phone',
            default    => 'file-text',
          };
          $typeColor = match($file->file_type) {
            'faq'      => 'bg-purple-100 text-purple-600',
            'services' => 'bg-yellow-100 text-yellow-600',
            'about'    => 'bg-blue-100 text-blue-600',
            'contact'  => 'bg-green-100 text-green-600',
            default    => 'bg-gray-100 text-gray-500',
          };
          $preview = mb_substr($file->content, 0, 180);
        @endphp
        <div class="kb-card bg-white rounded-2xl border border-gray-100 shadow-sm transition-all hover:border-[#4318FF]/20 hover:shadow-md"
             data-id="{{ $file->id }}" data-type="{{ $file->file_type }}">
          <div class="flex items-center gap-3 p-4">
            {{-- Drag handle --}}
            <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-gray-300 hover:text-gray-400 shrink-0" title="Drag to reorder">
              <i data-lucide="grip-vertical" class="w-4 h-4"></i>
            </div>

            {{-- Icon --}}
            <div class="w-10 h-10 bg-[#F4F7FE] rounded-xl flex items-center justify-center shrink-0">
              <i data-lucide="{{ $typeIcon }}" class="w-5 h-5 text-[#4318FF]"></i>
            </div>

            {{-- Name + type --}}
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-sm text-[#1B1B38] truncate">{{ $file->file_name }}</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $typeColor }}">{{ $file->file_type }}</span>
                @if(!$file->is_active)
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-400">Inactive</span>
                @endif
              </div>
              <p class="text-xs text-gray-400 mt-0.5">{{ number_format(strlen($file->content)) }} chars</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1 shrink-0">
              {{-- Expand/collapse --}}
              <button class="kb-toggle p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Preview">
                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
              </button>
              {{-- Toggle active --}}
              <form action="{{ route('admin.clients.knowledge-base.toggle', [$client->id, $file->id]) }}" method="POST">
                @csrf
                <button type="submit"
                  class="p-2 rounded-lg transition-all {{ $file->is_active ? 'text-[#05CD99] hover:bg-[#05CD99]/10' : 'text-gray-300 hover:bg-gray-50' }}"
                  title="{{ $file->is_active ? 'Deactivate' : 'Activate' }}">
                  <i data-lucide="{{ $file->is_active ? 'eye' : 'eye-off' }}" class="w-4 h-4"></i>
                </button>
              </form>
              {{-- Edit --}}
              <button onclick="editKb({{ $file->id }}, '{{ addslashes($file->file_name) }}', '{{ $file->file_type }}', {{ json_encode($file->content) }})"
                class="p-2 text-gray-400 hover:text-[#4318FF] hover:bg-[#4318FF]/5 rounded-lg transition-all" title="Edit">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
              </button>
              {{-- Delete --}}
              <form action="{{ route('admin.clients.knowledge-base.destroy', [$client->id, $file->id]) }}" method="POST"
                    onsubmit="return confirm('Delete \'{{ addslashes($file->file_name) }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 text-gray-400 hover:text-[#EE5D50] hover:bg-[#EE5D50]/5 rounded-lg transition-all" title="Delete">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </form>
            </div>
          </div>

          {{-- Collapsible preview --}}
          <div class="kb-preview hidden border-t border-gray-50 px-4 pb-4 pt-3">
            <pre class="text-xs text-gray-500 font-mono bg-[#F4F7FE] rounded-xl p-4 overflow-x-auto whitespace-pre-wrap max-h-48">{{ $preview }}@if(strlen($file->content) > 180)…@endif</pre>
          </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border-2 border-dashed border-gray-100 p-14 text-center">
          <i data-lucide="database" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
          <p class="font-semibold text-gray-400 mb-1">No knowledge base content yet</p>
          <p class="text-sm text-gray-300">Drop .md files above or click "Add Content"</p>
        </div>
        @endforelse
      </div>
    </div>

    {{-- ── Right: Sidebar ── --}}
    <div class="lg:col-span-1 space-y-4">

      {{-- Stats card --}}
      <div class="bg-[#1B1B38] rounded-3xl p-6 text-white shadow-xl">
        <div class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center mb-5">
          <i data-lucide="bot" class="w-5 h-5"></i>
        </div>
        <h4 class="text-lg font-bold mb-4">Bot Context</h4>

        <div class="space-y-3 mb-6">
          <div class="bg-white/5 rounded-xl p-3.5 border border-white/10">
            <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider mb-1">Total Files</p>
            <p class="text-2xl font-extrabold">{{ $kbFiles->count() }}</p>
          </div>
          <div class="bg-white/5 rounded-xl p-3.5 border border-white/10">
            <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider mb-1">Active Files</p>
            <p class="text-2xl font-extrabold">{{ $kbFiles->where('is_active', true)->count() }}</p>
          </div>
          <div class="bg-white/5 rounded-xl p-3.5 border border-white/10">
            <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider mb-1">Active Context</p>
            <p class="text-2xl font-extrabold">
              {{ number_format($kbFiles->where('is_active', true)->sum(fn($f) => strlen($f->content))) }}
              <span class="text-sm font-normal text-indigo-300">chars</span>
            </p>
          </div>
        </div>

        <a href="{{ route('admin.clients.conversations', $client->id) }}"
           class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
          <i data-lucide="message-circle" class="w-4 h-4"></i>
          View Conversations
        </a>
      </div>

      {{-- Quick actions --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-2">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Quick Actions</p>
        <a href="{{ route('admin.clients.edit', $client->id) }}"
           class="flex items-center gap-3 p-3 rounded-xl hover:bg-[#F4F7FE] text-gray-600 hover:text-[#4318FF] transition-all text-sm font-medium">
          <i data-lucide="edit-3" class="w-4 h-4"></i>
          Edit Client Profile
        </a>
        <a href="{{ route('admin.embed-scripts.show', $client->id) }}"
           class="flex items-center gap-3 p-3 rounded-xl hover:bg-[#F4F7FE] text-gray-600 hover:text-[#4318FF] transition-all text-sm font-medium">
          <i data-lucide="code-2" class="w-4 h-4"></i>
          Get Embed Code
        </a>
        <a href="{{ route('admin.clients.usage', $client->id) }}"
           class="flex items-center gap-3 p-3 rounded-xl hover:bg-[#F4F7FE] text-gray-600 hover:text-[#4318FF] transition-all text-sm font-medium">
          <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
          View Usage Stats
        </a>
      </div>
    </div>
  </div>
</div>

{{-- ════ ADD / EDIT MODAL ════ --}}
<dialog id="kb-modal" class="rounded-3xl p-0 bg-transparent backdrop:bg-black/50 max-w-2xl w-full">
  <div class="bg-white rounded-3xl overflow-hidden shadow-2xl">
    <form id="kb-form" method="POST" action="{{ route('admin.clients.knowledge-base.store', $client->id) }}">
      @csrf
      <input type="hidden" name="_method" id="form-method" value="POST">

      <div class="px-7 py-5 border-b border-gray-50 flex items-center justify-between">
        <h3 id="modal-title" class="text-lg font-bold text-[#1B1B38]">Add Knowledge Content</h3>
        <button type="button" onclick="document.getElementById('kb-modal').close()"
          class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <div class="px-7 py-6 space-y-5">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">File Name</label>
            <input type="text" name="file_name" id="field-name" required
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 outline-none"
              placeholder="e.g. pricing">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Content Type</label>
            <select name="file_type" id="field-type" required
              class="w-full bg-[#F4F7FE] border-none rounded-xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 outline-none cursor-pointer">
              <option value="about">About Business</option>
              <option value="services">Services / Pricing</option>
              <option value="faq">FAQ</option>
              <option value="contact">Contact Info</option>
              <option value="custom">Custom</option>
            </select>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Markdown Content</label>
            <label for="modal-file-upload"
              class="cursor-pointer flex items-center gap-1.5 text-[11px] font-bold text-[#4318FF] hover:text-indigo-700 transition-colors">
              <i data-lucide="upload" class="w-3.5 h-3.5"></i>
              Upload .md file
            </label>
            <input type="file" id="modal-file-upload" accept=".md,.markdown,.txt" class="hidden">
          </div>
          <textarea name="content" id="field-content" rows="13" required
            class="w-full bg-[#F4F7FE] border-none rounded-2xl px-4 py-3 text-sm text-[#1B1B38] focus:ring-2 focus:ring-[#4318FF]/20 font-mono outline-none resize-none"
            placeholder="# Heading&#10;&#10;Type your Markdown content here..."></textarea>
          <p class="text-[10px] text-gray-400 mt-1.5 text-right">
            <span id="char-count">0</span> characters
          </p>
        </div>
      </div>

      <div class="px-7 py-5 bg-gray-50 flex items-center justify-end gap-3">
        <button type="button" onclick="document.getElementById('kb-modal').close()"
          class="px-6 py-2.5 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors">
          Cancel
        </button>
        <button type="submit"
          class="bg-[#4318FF] text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all">
          Save Content
        </button>
      </div>
    </form>
  </div>
</dialog>

<style>
.kb-tab {
  padding: 0.4rem 0.9rem;
  border-radius: 0.625rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #9CA3AF;
  transition: all 0.15s;
  border: none;
  background: transparent;
  cursor: pointer;
}
.kb-tab.active, .kb-tab:hover {
  background: #4318FF;
  color: #fff;
}
.kb-tab.active span, .kb-tab:hover span {
  background: rgba(255,255,255,0.2);
  color: #fff;
}
.drag-handle { touch-action: none; }
.kb-card.dragging { opacity: 0.4; transform: scale(0.98); }
.kb-card.drag-over { border-color: #4318FF !important; background: #F4F7FE; }
#drop-zone.dragover { border-color: #4318FF; background: rgba(67,24,255,0.03); }
#drop-zone.dragover * { pointer-events: none; }
</style>

<script>
// ── Reorder URL ──
var REORDER_URL = '{{ route("admin.clients.knowledge-base.reorder", $client->id) }}';
var CSRF = '{{ csrf_token() }}';

// ── Modal helpers ──
function openAddModal() {
  var modal = document.getElementById('kb-modal');
  document.getElementById('kb-form').action = '{{ route("admin.clients.knowledge-base.store", $client->id) }}';
  document.getElementById('form-method').value = 'POST';
  document.getElementById('modal-title').textContent = 'Add Knowledge Content';
  document.getElementById('field-name').value = '';
  document.getElementById('field-type').value = 'about';
  document.getElementById('field-content').value = '';
  updateCharCount();
  modal.showModal();
}

function editKb(id, name, type, content) {
  var modal = document.getElementById('kb-modal');
  document.getElementById('kb-form').action = '/admin/clients/{{ $client->id }}/knowledge-base/' + id;
  document.getElementById('form-method').value = 'PUT';
  document.getElementById('modal-title').textContent = 'Edit Knowledge Content';
  document.getElementById('field-name').value = name;
  document.getElementById('field-type').value = type;
  document.getElementById('field-content').value = content;
  updateCharCount();
  modal.showModal();
}

// ── Char counter ──
function updateCharCount() {
  var el = document.getElementById('field-content');
  document.getElementById('char-count').textContent = el.value.length.toLocaleString();
}
document.getElementById('field-content').addEventListener('input', updateCharCount);

// ── Modal file upload ──
document.getElementById('modal-file-upload').addEventListener('change', function(e) {
  var file = e.target.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(ev) {
    document.getElementById('field-content').value = ev.target.result;
    updateCharCount();
    var nameField = document.getElementById('field-name');
    if (!nameField.value) nameField.value = file.name.replace(/\.[^.]+$/, '');
  };
  reader.readAsText(file);
  this.value = '';
});

// ── Filter tabs ──
document.querySelectorAll('.kb-tab').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.kb-tab').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    var type = btn.dataset.type;
    document.querySelectorAll('.kb-card').forEach(function(card) {
      card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
    });
  });
});

// ── Expand/collapse preview ──
document.querySelectorAll('.kb-toggle').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var card = btn.closest('.kb-card');
    var preview = card.querySelector('.kb-preview');
    var icon = btn.querySelector('[data-lucide]');
    preview.classList.toggle('hidden');
    icon.style.transform = preview.classList.contains('hidden') ? '' : 'rotate(180deg)';
  });
});

// ── Drag-and-drop REORDER ──
(function() {
  var list = document.getElementById('kb-list');
  var dragging = null;

  list.querySelectorAll('.kb-card').forEach(function(card) {
    var handle = card.querySelector('.drag-handle');

    handle.addEventListener('mousedown', function(e) {
      dragging = card;
      card.classList.add('dragging');
    });

    card.addEventListener('dragstart', function(e) {
      dragging = card;
      card.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    card.addEventListener('dragend', function() {
      card.classList.remove('dragging', 'drag-over');
      dragging = null;
      saveOrder();
    });
    card.addEventListener('dragover', function(e) {
      e.preventDefault();
      if (!dragging || dragging === card) return;
      var rect = card.getBoundingClientRect();
      var mid = rect.top + rect.height / 2;
      list.querySelectorAll('.kb-card').forEach(function(c) { c.classList.remove('drag-over'); });
      card.classList.add('drag-over');
      if (e.clientY < mid) {
        list.insertBefore(dragging, card);
      } else {
        list.insertBefore(dragging, card.nextSibling);
      }
    });
    card.addEventListener('dragleave', function() {
      card.classList.remove('drag-over');
    });
    card.addEventListener('drop', function(e) {
      e.preventDefault();
      card.classList.remove('drag-over');
    });

    card.setAttribute('draggable', 'true');
  });

  function saveOrder() {
    var ids = [];
    list.querySelectorAll('.kb-card').forEach(function(c) { ids.push(c.dataset.id); });
    fetch(REORDER_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ order: ids })
    });
  }
})();

// ── Drop zone: drag .md files from desktop ──
(function() {
  var zone = document.getElementById('drop-zone');
  var fileInput = document.getElementById('dz-file-input');

  zone.addEventListener('click', function(e) {
    if (!e.target.matches('label,label *')) fileInput.click();
  });

  ['dragenter','dragover'].forEach(function(ev) {
    zone.addEventListener(ev, function(e) {
      e.preventDefault();
      zone.classList.add('dragover');
    });
  });
  ['dragleave','drop'].forEach(function(ev) {
    zone.addEventListener(ev, function(e) {
      e.preventDefault();
      zone.classList.remove('dragover');
    });
  });
  zone.addEventListener('drop', function(e) {
    e.preventDefault();
    processFiles(e.dataTransfer.files);
  });
  fileInput.addEventListener('change', function() {
    processFiles(this.files);
    this.value = '';
  });

  function processFiles(files) {
    Array.from(files).forEach(function(file) {
      if (!file.name.match(/\.(md|markdown|txt)$/i)) return;
      var reader = new FileReader();
      reader.onload = function(ev) {
        openAddModal();
        document.getElementById('field-name').value = file.name.replace(/\.[^.]+$/, '');
        document.getElementById('field-content').value = ev.target.result;
        updateCharCount();
      };
      reader.readAsText(file);
    });
  }
})();
</script>
@endsection
