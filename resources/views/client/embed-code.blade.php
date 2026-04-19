@extends('layouts.client')
@section('title', 'Embed Code')
@section('header', 'Installation')

@section('content')
<div class="max-w-4xl space-y-8">
    <!-- Intro Card -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex items-center gap-6">
        <div class="w-16 h-16 bg-[#F4F7FE] rounded-2xl flex items-center justify-center">
            <i data-lucide="code-2" class="text-[#4318FF] w-8 h-8"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-[#1B1B38]">Ready to go live?</h3>
            <p class="text-sm text-gray-400">Copy the script below and paste it into your website's header or footer.</p>
        </div>
    </div>

    <!-- Script Block -->
    <div class="bg-[#1B1B38] rounded-3xl p-8 shadow-xl relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
            <i data-lucide="terminal" class="w-32 h-32 text-white"></i>
        </div>
        
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-[#EE5D50]"></div>
                <div class="w-3 h-3 rounded-full bg-[#FFB547]"></div>
                <div class="w-3 h-3 rounded-full bg-[#05CD99]"></div>
                <span class="ml-2 text-[10px] font-bold text-gray-500 uppercase tracking-widest">widget-script.html</span>
            </div>
            <button onclick="copyScript()" class="flex items-center gap-2 text-[10px] font-bold text-indigo-400 uppercase tracking-wider hover:text-white transition-colors">
                <i data-lucide="copy" class="w-3 h-3"></i>
                <span id="copy-text">Copy Code</span>
            </button>
        </div>

        <div class="bg-white/5 rounded-2xl p-6 font-mono text-sm text-indigo-100 leading-relaxed border border-white/10">
            <code id="embed-script">&lt;!-- ChatBot Nepal Widget --&gt;
&lt;script 
  src="{{ config('app.url') }}/widget.js" 
  data-token="{{ auth()->user()->api_token }}"
  defer&gt;
&lt;/script&gt;
&lt;!-- End ChatBot Nepal Widget --&gt;</code>
        </div>
    </div>

    <!-- Widget Configuration Form -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-violet-50 rounded-2xl flex items-center justify-center">
                <i data-lucide="settings-2" class="text-violet-600 w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-lg font-bold text-[#1B1B38]">Chatbot Features</h4>
                <p class="text-sm text-gray-400">Enable or disable features for your chatbot</p>
            </div>
        </div>

        <form action="{{ route('client.embed-code.update') }}" method="POST">
            @csrf

            {{-- Hidden fields to carry over existing config values --}}
            <input type="hidden" name="welcome_message" value="{{ $config->welcome_message ?? 'Namaste! How can I help you today?' }}">
            <input type="hidden" name="primary_color" value="{{ $config->primary_color ?? '#4F46E5' }}">
            <input type="hidden" name="position" value="{{ $config->position ?? 'bottom-right' }}">
            <input type="hidden" name="bot_name" value="{{ $config->bot_name ?? 'Assistant' }}">
            <input type="hidden" name="show_powered_by" value="{{ $config->show_powered_by ?? 1 }}">

            <label class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all group">
                <div class="relative mt-0.5 flex-shrink-0">
                    <input type="hidden" name="prechat_enabled" value="0">
                    <input type="checkbox" name="prechat_enabled" value="1" id="prechat_toggle"
                           {{ ($config->prechat_enabled ?? false) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-checked:bg-indigo-600 rounded-full transition-colors duration-200"></div>
                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-[#1B1B38] text-sm group-hover:text-indigo-700 transition-colors">Pre-Chat Visitor Form</p>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">Show a brief form asking visitors for their name, email, and phone number before the first message. All fields are optional — visitors can skip at any time.</p>
                </div>
            </label>

            @if(session('success'))
            <div class="mt-4 bg-[#E2FFF3] border border-[#05CD99]/20 text-[#05CD99] rounded-xl px-4 py-3 text-sm font-semibold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                {{ session('success') }}
            </div>
            @endif

            <div class="mt-5 flex justify-end">
                <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 active:scale-95 transition-all shadow-[0_4px_14px_rgba(79,70,229,0.35)]">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Instructions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <div class="w-12 h-12 bg-[#E2FFF3] rounded-2xl flex items-center justify-center mb-6">
                <i data-lucide="layers" class="text-[#05CD99] w-6 h-6"></i>
            </div>
            <h4 class="text-lg font-bold text-[#1B1B38] mb-4">How to install</h4>
            <ul class="space-y-4">
                <li class="flex gap-3 text-sm text-gray-500">
                    <span class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold shrink-0">1</span>
                    Copy the unique script tag from the dark block above.
                </li>
                <li class="flex gap-3 text-sm text-gray-500">
                    <span class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold shrink-0">2</span>
                    Open your website's HTML file (e.g., index.html).
                </li>
                <li class="flex gap-3 text-sm text-gray-500">
                    <span class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold shrink-0">3</span>
                    Paste the code just before the closing <code>&lt;/body&gt;</code> tag.
                </li>
            </ul>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <div class="w-12 h-12 bg-[#FFF5E9] rounded-2xl flex items-center justify-center mb-6">
                <i data-lucide="layout" class="text-[#FFB547] w-6 h-6"></i>
            </div>
            <h4 class="text-lg font-bold text-[#1B1B38] mb-4">Platform Guides</h4>
            <div class="grid grid-cols-2 gap-3">
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-500 hover:border-[#4318FF] hover:text-[#4318FF] transition-all">WordPress</button>
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-500 hover:border-[#4318FF] hover:text-[#4318FF] transition-all">Wix / Shopify</button>
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-500 hover:border-[#4318FF] hover:text-[#4318FF] transition-all">Laravel / PHP</button>
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-500 hover:border-[#4318FF] hover:text-[#4318FF] transition-all">React / Vue</button>
            </div>
        </div>
    </div>
</div>

<script>
    function copyScript() {
        const text = document.getElementById('embed-script').innerText;
        const copyBtn = document.getElementById('copy-text');
        
        navigator.clipboard.writeText(text).then(() => {
            copyBtn.innerText = 'Copied!';
            copyBtn.classList.add('text-[#05CD99]');
            setTimeout(() => {
                copyBtn.innerText = 'Copy Code';
                copyBtn.classList.remove('text-[#05CD99]');
            }, 2000);
        });
    }
</script>
@endsection
