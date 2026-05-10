<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $hostedPage->title }}</title>
  <meta property="og:title" content="{{ $hostedPage->title }}" />
  <meta property="og:description" content="{{ $ogDescription }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
          },
          colors: {
            brand: {
              primary: '{{ $hostedPage->brand_color ?? "#3B1FA8" }}',
              dark: '#2D1680',
              accent: '#6C47FF',
            }
          }
        }
      }
    }
  </script>
  <style>
    :root {
      --brand-primary: {{ $hostedPage->brand_color ?? '#3B1FA8' }};
      --brand-dark: #2D1680;
      --brand-accent: #6C47FF;
    }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #F3F4F8;
    }
    .scroll-smooth { scroll-behavior: smooth; }
    .typing-dot {
      animation: bounce 1.4s infinite ease-in-out both;
    }
    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }
    .typing-dot:nth-child(3) { animation-delay: 0s; }
    @keyframes bounce {
      0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
      40% { transform: scale(1); opacity: 1; }
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    .animate-pulse-slow { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
  </style>
</head>
<body class="min-h-screen">
  @php
    $brandColor = $hostedPage->brand_color ?? '#3B1FA8';
    $quickActions = $hostedPage->quick_actions ?? [];
    $bizName = $hostedPage->title;
    $firstLetter = strtoupper(substr($bizName, 0, 1));
  @endphp

  <!-- Header -->
  <header class="fixed top-0 left-0 right-0 z-50 h-[72px] bg-[var(--brand-primary)] px-4 lg:px-6 flex items-center justify-between shadow-lg">
    <div class="flex items-center gap-3">
      @if($hostedPage->logo_url)
        <img src="{{ $hostedPage->logo_url }}" alt="Logo" class="w-12 h-12 rounded-xl object-cover" />
      @else
        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white text-xl font-bold">
          {{ $firstLetter }}
        </div>
      @endif
      <div>
        <div class="text-white font-bold text-xl">{{ $bizName }}</div>
        <div class="text-white/80 text-sm italic">{{ $hostedPage->tagline }}</div>
      </div>
    </div>
    <div class="flex items-center gap-2 text-white text-sm">
      <span class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse-slow"></span>
      <span>AI Assistant Online</span>
    </div>
  </header>

  <!-- Mobile Info Button -->
  <button id="mobileInfoBtn" class="fixed top-[80px] left-3 z-40 lg:hidden bg-white rounded-full p-2 shadow-lg border border-gray-200" aria-label="Show business info">
    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  </button>

  <!-- Main Layout -->
  <div class="pt-[72px] pb-4 lg:pb-6 min-h-screen">
    <div class="max-w-7xl mx-auto px-2 lg:px-4">
      <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr_320px] gap-4 lg:gap-6">
        
        <!-- Left Sidebar -->
        <aside id="sidebar" class="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 h-fit sticky top-4">
          @if($hostedPage->cover_image_url)
            <img src="{{ $hostedPage->cover_image_url }}" alt="Welcome" class="w-full h-[180px] rounded-lg object-cover border border-gray-200 mb-4" />
          @else
            <div class="w-full h-[180px] rounded-lg bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] mb-4 flex items-center justify-center">
              <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          @endif
          
          <h3 class="text-lg font-bold text-gray-900 mb-2">Welcome to {{ $bizName }} 👋</h3>
          <p class="text-sm text-gray-600 mb-4">{{ $hostedPage->welcome_message }}</p>

          <div class="text-xs uppercase font-semibold text-[var(--brand-primary)] mb-3">Quick Actions</div>
          <div class="space-y-2">
            @foreach($quickActions as $action)
              <button class="quick-action-btn flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-200 hover:bg-purple-50 hover:border-purple-200 transition-all text-sm font-medium text-gray-700" data-prompt="{{ $action['label'] }}">
                <div class="flex items-center gap-2">
                  @switch($action['icon'] ?? 'chat')
                    @case('calendar')
                      <svg class="w-4 h-4 text-[var(--brand-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                      @break
                    @case('currency')
                      <svg class="w-4 h-4 text-[var(--brand-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      @break
                    @case('location')
                      <svg class="w-4 h-4 text-[var(--brand-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                      @break
                    @case('phone')
                      <svg class="w-4 h-4 text-[var(--brand-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                      @break
                    @default
                      <svg class="w-4 h-4 text-[var(--brand-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                  @endswitch
                  <span>{{ $action['label'] }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
              </button>
            @endforeach
          </div>

          <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 text-center">✨ Powered by iSoftro AI</p>
          </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div id="mobileSidebar" class="fixed inset-0 z-50 bg-black/50 hidden lg:hidden">
          <div class="absolute left-0 top-0 bottom-0 w-80 bg-white shadow-2xl transform -translate-x-full transition-transform" id="sidebarPanel">
            <div class="p-4">
              <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Business Info</h3>
                <button id="closeSidebar" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
              @if($hostedPage->cover_image_url)
                <img src="{{ $hostedPage->cover_image_url }}" alt="Welcome" class="w-full h-32 rounded-lg object-cover mb-4" />
              @endif
              <h4 class="font-bold mb-2">Welcome to {{ $bizName }} 👋</h4>
              <p class="text-sm text-gray-600 mb-4">{{ $hostedPage->welcome_message }}</p>
              <div class="text-xs uppercase font-semibold text-[var(--brand-primary)] mb-3">Quick Actions</div>
              <div class="space-y-2">
                @foreach($quickActions as $action)
                  <button class="quick-action-btn mobile-quick-btn flex items-center justify-between w-full px-3 py-2.5 rounded-lg border border-gray-200 hover:bg-purple-50 text-sm font-medium" data-prompt="{{ $action['label'] }}">
                    <span>{{ $action['label'] }}</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  </button>
                @endforeach
              </div>
              <p class="text-xs text-gray-400 text-center mt-6">✨ Powered by iSoftro AI</p>
            </div>
          </div>
        </div>

        <!-- Chat Area -->
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col lg:h-[calc(100vh-120px)]">
          <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth">
            <!-- Messages will be inserted here -->
            <div id="welcomeMsg" class="flex gap-3">
              <div class="w-9 h-9 rounded-full bg-[var(--brand-primary)] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
              </div>
              <div class="bg-white border border-gray-100 rounded-2xl rounded-tl-none px-4 py-3 max-w-[80%] shadow-sm">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $hostedPage->welcome_message }}</p>
                <p class="text-xs text-gray-400 mt-1 text-right">Now</p>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div id="typingIndicator" class="hidden flex gap-3">
              <div class="w-9 h-9 rounded-full bg-[var(--brand-primary)] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
              </div>
              <div class="bg-white border border-gray-100 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                <div class="flex gap-1">
                  <span class="typing-dot w-2 h-2 rounded-full bg-gray-400"></span>
                  <span class="typing-dot w-2 h-2 rounded-full bg-gray-400"></span>
                  <span class="typing-dot w-2 h-2 rounded-full bg-gray-400"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Suggestion Chips -->
          <div id="suggestionChips" class="hidden px-4 py-2 flex flex-wrap gap-2 border-t border-gray-100">
            <!-- Will be populated dynamically -->
          </div>

          <!-- Input Area -->
          <div class="p-4 border-t border-gray-100 bg-white rounded-b-2xl">
            <div class="flex items-center gap-2 bg-gray-50 rounded-xl border border-gray-200 px-3 py-2">
              <button class="p-2 text-gray-400 hover:text-gray-600" aria-label="Attach file">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
              </button>
              <input type="text" id="chatInput" placeholder="Type your message..." class="flex-1 bg-transparent border-none outline-none text-gray-800 placeholder-gray-400" aria-label="Type your message" />
              <button id="sendBtn" class="w-10 h-10 rounded-xl bg-[var(--brand-primary)] flex items-center justify-center hover:opacity-90 transition text-white" aria-label="Send message">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </button>
            </div>
            <p class="text-xs text-gray-400 text-center mt-2">We typically reply instantly ⚡</p>
          </div>
        </section>

        <!-- Right Lead Capture (Desktop) -->
        <aside class="hidden lg:block" id="leadContainer">
          <div id="leadCard" class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 sticky top-4 hidden">
            <button id="leadClose" class="absolute top-4 right-4 p-1 text-gray-400 hover:text-gray-600" aria-label="Close lead form">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div id="leadFormContent">
              <h4 class="font-bold text-xl mb-1">Almost there! 😊</h4>
              <p class="text-sm text-gray-500 mb-4">Please share your details so we can assist you better.</p>
              <form id="leadForm" class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                  <input type="text" id="leadName" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent" placeholder="Enter your name" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                  <div class="flex">
                    <div class="flex items-center px-3 bg-gray-50 border border-r-0 border-gray-200 rounded-l-lg">
                      <span class="text-lg">🇳🇵</span>
                      <span class="text-sm text-gray-600 ml-1">+977</span>
                    </div>
                    <input type="tel" id="leadPhone" required class="flex-1 px-3 py-2.5 border border-gray-200 rounded-r-lg text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent" placeholder="98XXXXXXXX" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-gray-400">(optional)</span></label>
                  <input type="email" id="leadEmail" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent" placeholder="your@email.com" />
                </div>
                <button type="submit" class="w-full py-3 bg-[var(--brand-primary)] text-white font-semibold rounded-xl hover:opacity-90 transition">
                  Submit
                </button>
              </form>
              <p class="text-xs text-gray-400 text-center mt-3">You can continue chatting after this.</p>
            </div>
            <div id="leadSuccess" class="hidden text-center py-4">
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              </div>
              <p class="font-semibold text-gray-800">Thanks! We'll be in touch soon ✓</p>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <!-- Mobile Lead Card (Floating) -->
  <div id="mobileLeadCard" class="fixed bottom-20 left-4 right-4 z-40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-5 border border-gray-100 relative">
      <button id="mobileLeadClose" class="absolute top-3 right-3 p-1 text-gray-400 hover:text-gray-600" aria-label="Close lead form">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
      <div id="mobileLeadContent">
        <h4 class="font-bold text-lg mb-1">Almost there! 😊</h4>
        <p class="text-sm text-gray-500 mb-4">Share your details for better assistance.</p>
        <form id="mobileLeadForm" class="space-y-3">
          <input type="text" id="mobileLeadName" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="Your name" />
          <div class="flex">
            <div class="flex items-center px-2 bg-gray-50 border border-r-0 border-gray-200 rounded-l-lg text-sm">
              <span>🇳🇵</span>
              <span class="ml-1">+977</span>
            </div>
            <input type="tel" id="mobileLeadPhone" required class="flex-1 px-2 py-2 border border-gray-200 rounded-r-lg text-sm" placeholder="98XXXXXXXX" />
          </div>
          <input type="email" id="mobileLeadEmail" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="Email (optional)" />
          <button type="submit" class="w-full py-2.5 bg-[var(--brand-primary)] text-white font-semibold rounded-xl">
            Submit
          </button>
        </form>
      </div>
      <div id="mobileLeadSuccess" class="hidden text-center py-3">
        <p class="font-semibold text-green-600">Thanks! We'll be in touch ✓</p>
      </div>
    </div>
  </div>

  <script>
    const slug = @json($hostedPage->slug);
    const brandColor = @json($brandColor);
    const messagesContainer = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const typingIndicator = document.getElementById('typingIndicator');
    const suggestionChips = document.getElementById('suggestionChips');
    const leadCard = document.getElementById('leadCard');
    const leadClose = document.getElementById('leadClose');
    const mobileLeadCard = document.getElementById('mobileLeadCard');
    const mobileLeadClose = document.getElementById('mobileLeadClose');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const sidebarPanel = document.getElementById('sidebarPanel');
    const isMobile = window.matchMedia('(max-width: 1023px)').matches;

    let sessionId = null;
    let sessionToken = null;
    let messageCount = 0;
    let leadSubmitted = false;
    let leadDismissed = false;

    function getFingerprint() {
      const key = 'hosted_chat_fingerprint_v1';
      let fp = localStorage.getItem(key);
      if (!fp) {
        fp = 'fp_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
        localStorage.setItem(key, fp);
      }
      return fp;
    }

    function getTimestamp() {
      const now = new Date();
      return now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
    }

    function scrollToBottom() {
      messagesContainer.scrollTo({ top: messagesContainer.scrollHeight, behavior: 'smooth' });
    }

    function createUserMessage(text) {
      const div = document.createElement('div');
      div.className = 'flex justify-end';
      div.innerHTML = `
        <div class="bg-[var(--brand-primary)] text-white rounded-2xl rounded-tr-none px-4 py-3 max-w-[80%]">
          <p class="whitespace-pre-wrap">${escapeHtml(text)}</p>
          <p class="text-[10px] text-white/70 mt-1 text-right">${getTimestamp()}</p>
        </div>
      `;
      return div;
    }

    function createBotMessage(text, suggestions = []) {
      const div = document.createElement('div');
      div.className = 'flex gap-3';
      div.innerHTML = `
        <div class="w-9 h-9 rounded-full bg-[var(--brand-primary)] flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
          </svg>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl rounded-tl-none px-4 py-3 max-w-[80%] shadow-sm">
          <p class="text-gray-800 whitespace-pre-wrap">${escapeHtml(text)}</p>
          <p class="text-xs text-gray-400 mt-1 text-right">${getTimestamp()}</p>
        </div>
      `;
      return div;
    }

    function createSuggestionChips(suggestions) {
      if (!Array.isArray(suggestions) || suggestions.length === 0) return;
      suggestionChips.innerHTML = '';
      suggestions.forEach(suggestion => {
        const btn = document.createElement('button');
        btn.className = 'px-3 py-1.5 text-sm border border-gray-200 rounded-full hover:bg-purple-50 hover:border-purple-200 transition-colors';
        btn.textContent = suggestion;
        btn.addEventListener('click', () => sendMessage(suggestion));
        suggestionChips.appendChild(btn);
      });
      suggestionChips.classList.remove('hidden');
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function appendMessage(role, text, suggestions = []) {
      const msg = role === 'user' ? createUserMessage(text) : createBotMessage(text, suggestions);
      messagesContainer.insertBefore(msg, typingIndicator);
      scrollToBottom();
    }

    function showTyping() {
      typingIndicator.classList.remove('hidden');
      scrollToBottom();
    }

    function hideTyping() {
      typingIndicator.classList.add('hidden');
    }

    function showErrorMessage(text) {
      const msg = createBotMessage(text);
      messagesContainer.insertBefore(msg, typingIndicator);
      scrollToBottom();
    }

    function maybeShowLeadCapture() {
      if (leadSubmitted || leadDismissed) return;
      if (messageCount >= 2) {
        if (isMobile) {
          mobileLeadCard.classList.remove('hidden');
        } else {
          leadCard.classList.remove('hidden');
        }
      }
    }

    function dismissLeadCard() {
      leadDismissed = true;
      if (isMobile) {
        mobileLeadCard.classList.add('hidden');
      } else {
        leadCard.classList.add('hidden');
      }
    }

    function showLeadSuccess() {
      leadSubmitted = true;
      if (isMobile) {
        document.getElementById('mobileLeadContent').classList.add('hidden');
        document.getElementById('mobileLeadSuccess').classList.remove('hidden');
        setTimeout(() => mobileLeadCard.classList.add('hidden'), 2000);
      } else {
        document.getElementById('leadFormContent').classList.add('hidden');
        document.getElementById('leadSuccess').classList.remove('hidden');
      }
    }

    async function initChat() {
      try {
        const res = await fetch('/api/chat/init', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            slug, 
            channel: 'hosted_page',
            visitor_fingerprint: getFingerprint(),
            source_url: window.location.href
          })
        });
        const data = await res.json();
        if (data.success) {
          sessionId = data.session_id;
          sessionToken = data.session_token;
        } else {
          showErrorMessage('Service temporarily unavailable. Please refresh the page.');
        }
      } catch (e) {
        showErrorMessage('Sorry, I\'m having trouble connecting. Please try again in a moment.');
      }
    }

    async function sendMessage(text) {
      if (!text || !sessionId || !sessionToken) return;
      suggestionChips.classList.add('hidden');
      appendMessage('user', text);
      showTyping();

      try {
        const res = await fetch('/api/chat/message', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            session_id: sessionId,
            session_token: sessionToken,
            message: text,
            visitor_fingerprint: getFingerprint(),
            source_url: window.location.href
          })
        });
        
        const data = await res.json();
        hideTyping();
        
        if (!res.ok) {
          if (res.status === 429) {
            showErrorMessage('You\'re sending messages too quickly. Please wait a moment.');
          } else {
            showErrorMessage('Sorry, I\'m having trouble connecting. Please try again in a moment.');
          }
          return;
        }
        
        if (data.success) {
          appendMessage('bot', data.reply || 'I\'m not sure how to respond to that.');
          if (data.suggestions && data.suggestions.length > 0) {
            createSuggestionChips(data.suggestions);
          } else if (data.buttons && data.buttons.length > 0) {
            const suggestions = data.buttons
              .filter(b => b.type === 'reply')
              .map(b => b.label);
            if (suggestions.length > 0) {
              createSuggestionChips(suggestions);
            }
          }
          messageCount++;
          maybeShowLeadCapture();
        } else {
          showErrorMessage(data.error || 'Sorry, your message could not be processed.');
        }
      } catch (e) {
        hideTyping();
        showErrorMessage('Sorry, I\'m having trouble connecting. Please try again in a moment.');
      }
    }

    async function submitLead(name, phone, email) {
      if (!sessionId || !sessionToken) return;
      
      try {
        const res = await fetch('/api/chat/lead', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            session_id: sessionId,
            session_token: sessionToken,
            visitor_fingerprint: getFingerprint(),
            name,
            phone,
            email,
            trigger: 'chat_lead_capture'
          })
        });
        const data = await res.json();
        if (data.success) {
          showLeadSuccess();
        }
      } catch (e) {
        console.error('Lead submission error:', e);
      }
    }

    // Event Listeners
    sendBtn.addEventListener('click', () => {
      const text = chatInput.value.trim();
      chatInput.value = '';
      sendMessage(text);
    });

    chatInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendBtn.click();
      }
    });

    document.querySelectorAll('.quick-action-btn').forEach(btn => {
      btn.addEventListener('click', () => sendMessage(btn.dataset.prompt));
    });

    document.querySelectorAll('.mobile-quick-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        sendMessage(btn.dataset.prompt);
        mobileSidebar.classList.add('hidden');
        sidebarPanel.classList.add('-translate-x-full');
      });
    });

    leadClose.addEventListener('click', dismissLeadCard);
    mobileLeadClose.addEventListener('click', dismissLeadCard);

    document.getElementById('leadForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('leadName').value.trim();
      const phone = document.getElementById('leadPhone').value.trim();
      const email = document.getElementById('leadEmail').value.trim();
      if (name && phone) {
        submitLead(name, '+977' + phone, email);
      }
    });

    document.getElementById('mobileLeadForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('mobileLeadName').value.trim();
      const phone = document.getElementById('mobileLeadPhone').value.trim();
      const email = document.getElementById('mobileLeadEmail').value.trim();
      if (name && phone) {
        submitLead(name, '+977' + phone, email);
      }
    });

    // Mobile sidebar handlers
    document.getElementById('mobileInfoBtn').addEventListener('click', () => {
      mobileSidebar.classList.remove('hidden');
      sidebarPanel.classList.remove('-translate-x-full');
    });

    mobileSidebar.addEventListener('click', (e) => {
      if (e.target === mobileSidebar) {
        mobileSidebar.classList.add('hidden');
        sidebarPanel.classList.add('-translate-x-full');
      }
    });

    document.getElementById('closeSidebar').addEventListener('click', () => {
      mobileSidebar.classList.add('hidden');
      sidebarPanel.classList.add('-translate-x-full');
    });

    // Initialize
    initChat();
  </script>
</body>
</html>