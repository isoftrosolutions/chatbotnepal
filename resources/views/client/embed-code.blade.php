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
                <h4 class="text-lg font-bold text-[#1B1B38]">Chatbot Customization</h4>
                <p class="text-sm text-gray-400">Customize your chatbot appearance and branding</p>
            </div>
        </div>

        <form action="{{ route('client.embed-code.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Chatbot Launcher & Position Settings -->
            <div class="mb-6 p-4 rounded-2xl border border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-indigo-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="palette" class="text-indigo-600 w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#1B1B38] text-sm">Chatbot Launcher</p>
                        <p class="text-xs text-gray-400">Customize the launcher button color and position</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Launcher Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="primary_color" id="primary_color_picker"
                                   value="{{ $config->primary_color ?? '#006d77' }}"
                                   class="w-12 h-12 rounded-xl border border-gray-200 cursor-pointer overflow-hidden">
                            <input type="text" name="primary_color_text" id="primary_color_text"
                                   value="{{ $config->primary_color ?? '#006d77' }}"
                                   class="flex-1 px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm font-mono uppercase"
                                   pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Position</label>
                        <select name="position" class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                            <option value="bottom-right" {{ ($config->position ?? 'bottom-right') == 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                            <option value="bottom-left" {{ ($config->position ?? 'bottom-right') == 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bot Identity Section -->
            <div class="mb-6 p-4 rounded-2xl border border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-teal-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="bot" class="text-teal-600 w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#1B1B38] text-sm">Bot Identity</p>
                        <p class="text-xs text-gray-400">Name, tagline, and contact info shown inside the widget</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Bot Name</label>
                        <input type="text" name="bot_name"
                               value="{{ $config->bot_name ?? 'Assistant' }}"
                               placeholder="e.g. Aria, Max, Support Bot"
                               class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Tagline <span class="text-gray-400 font-normal normal-case">(shown in pre-chat form)</span></label>
                        <input type="text" name="tagline"
                               value="{{ $config->tagline ?? '' }}"
                               placeholder="e.g. We reply within minutes"
                               class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Support Email <span class="text-gray-400 font-normal normal-case">(escalation CTA)</span></label>
                        <input type="email" name="support_email"
                               value="{{ $config->support_email ?? '' }}"
                               placeholder="support@yourcompany.com"
                               class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Privacy Policy URL <span class="text-gray-400 font-normal normal-case">(shown in pre-chat form)</span></label>
                        <input type="url" name="privacy_policy_url"
                               value="{{ $config->privacy_policy_url ?? '' }}"
                               placeholder="https://yoursite.com/privacy"
                               class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                    </div>
                </div>
            </div>

            <!-- Company Logo Section -->
            <div class="mb-6 p-4 rounded-2xl border border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="image" class="text-blue-600 w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#1B1B38] text-sm">Company Logo</p>
                        <p class="text-xs text-gray-400">Upload your company logo to display in the chat header</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="file" name="company_logo" accept="image/*" id="company_logo"
                               class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                        <p class="text-xs text-gray-400 mt-1">Supported formats: JPEG, PNG, JPG, GIF, SVG (Max: 2MB)</p>
                    </div>
                    @if($config->company_logo_url ?? null)
                    <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden">
                        <img src="{{ $config->company_logo_url }}" alt="Company Logo" class="w-full h-full object-cover">
                    </div>
                    @endif
                </div>
            </div>

            <!-- Watermark Background Section -->
            <div class="mb-6 p-4 rounded-2xl border border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="layers" class="text-purple-600 w-4 h-4"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-[#1B1B38] text-sm">Watermark Background</p>
                        <p class="text-xs text-gray-400">Display your logo as a subtle watermark in the chat background</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="hidden" name="watermark_enabled" value="0">
                        <input type="checkbox" name="watermark_enabled" value="1" id="watermark_toggle"
                               {{ ($config->watermark_enabled ?? false) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-purple-600 rounded-full transition-colors duration-200"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ ($config->watermark_enabled ?? false) ? '' : 'hidden' }}" id="watermark-settings">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Opacity</label>
                        <input type="range" name="watermark_opacity" min="0.05" max="0.5" step="0.05"
                               value="{{ $config->watermark_opacity ?? 0.1 }}"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                               oninput="document.getElementById('opacity-value').textContent = this.value">
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>5%</span>
                            <span id="opacity-value">{{ ($config->watermark_opacity ?? 0.1) * 100 }}%</span>
                            <span>50%</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-600 mb-2">Position</label>
                        <select name="watermark_position" class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all text-sm">
                            <option value="center" {{ ($config->watermark_position ?? 'center') == 'center' ? 'selected' : '' }}>Center</option>
                            <option value="top-left" {{ ($config->watermark_position ?? 'center') == 'top-left' ? 'selected' : '' }}>Top Left</option>
                            <option value="top-right" {{ ($config->watermark_position ?? 'center') == 'top-right' ? 'selected' : '' }}>Top Right</option>
                            <option value="bottom-left" {{ ($config->watermark_position ?? 'center') == 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                            <option value="bottom-right" {{ ($config->watermark_position ?? 'center') == 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Suggested Questions -->
            <div class="mb-4 p-4 rounded-2xl border border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-emerald-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="message-circle-question" class="text-emerald-600 w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#1B1B38] text-sm">Quick-Reply Suggestions</p>
                        <p class="text-xs text-gray-400">Chips shown below the welcome message. One question per line (max 4).</p>
                    </div>
                </div>
                <textarea name="suggested_questions_raw" rows="4"
                    placeholder="What are your fees?&#10;Where are you located?&#10;How do I enroll?&#10;Do you offer a free trial?"
                    class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-teal-100 focus:border-teal-500 outline-none transition-all text-sm font-mono resize-none">{{ implode("\n", $config->suggested_questions ?? []) }}</textarea>
            </div>

            <!-- Message Timestamps Toggle -->
            <label class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border border-gray-100 hover:border-teal-200 hover:bg-teal-50/30 transition-all group mb-3">
                <div class="relative mt-0.5 flex-shrink-0">
                    <input type="hidden" name="message_meta_enabled" value="0">
                    <input type="checkbox" name="message_meta_enabled" value="1" id="meta_toggle"
                           {{ ($config->message_meta_enabled ?? false) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-checked:bg-teal-600 rounded-full transition-colors duration-200"></div>
                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-[#1B1B38] text-sm group-hover:text-teal-700 transition-colors">Show Message Timestamps</p>
                    <p class="text-xs text-gray-400 mt-1 leading-relaxed">Display send time and read-receipt ticks on each message bubble (WhatsApp style).</p>
                </div>
            </label>

            <!-- Pre-Chat Form Toggle -->
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
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-400 cursor-not-allowed opacity-50" disabled>WordPress</button>
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-400 cursor-not-allowed opacity-50" disabled>Wix / Shopify</button>
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-400 cursor-not-allowed opacity-50" disabled>Laravel / PHP</button>
                <button class="p-3 border border-gray-100 rounded-xl text-xs font-bold text-gray-400 cursor-not-allowed opacity-50" disabled>React / Vue</button>
            </div>
            <p class="text-xs text-gray-400 mt-3 text-center">Platform guides coming soon</p>
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

    // Color picker sync
    const colorPicker = document.getElementById('primary_color_picker');
    const colorText = document.getElementById('primary_color_text');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });
    
    colorText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorPicker.value = this.value;
        }
    });

    // Watermark settings toggle
    document.getElementById('watermark_toggle').addEventListener('change', function() {
        const settings = document.getElementById('watermark-settings');
        if (this.checked) {
            settings.classList.remove('hidden');
        } else {
            settings.classList.add('hidden');
        }
    });
</script>
@endsection
