<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - ChatBot Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand': '#18E299',
                        'brand-light': '#d4fae8',
                        'brand-deep': '#0fa76e',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in { animation: slide-in 0.3s ease-out; }

        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in { animation: fade-in 0.2s ease-out; }
    </style>
</head>
<body class="bg-[#fafafa] text-[#0d0d0d]" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Overlay -->
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-40 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside
            class="w-64 bg-white border-r border-[rgba(0,0,0,0.05)] flex flex-col shrink-0 fixed lg:relative inset-y-0 left-0 z-50 transform transition-transform duration-300 lg:transform-none"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false" class="lg:hidden absolute top-4 right-4 p-2 rounded-lg hover:bg-[#f5f5f5] transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-[#0d0d0d]"></i>
            </button>

            <div class="p-6 flex items-center gap-3">
                <div class="w-9 h-9 bg-[#0d0d0d] rounded-[9999px] flex items-center justify-center">
                    <i data-lucide="bot" class="text-white w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-[#0d0d0d] font-semibold text-base leading-none">ChatBot Nepal</h1>
                    <p class="text-[10px] text-[#666666] font-medium tracking-[0.65px] uppercase mt-1">Client Portal</p>
                </div>
            </div>

            <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
                <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('client.dashboard') ? 'bg-[#0d0d0d] text-white' : 'text-[#333333] hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('client.conversations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('client.conversations*') ? 'bg-[#0d0d0d] text-white' : 'text-[#333333] hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Chat History</span>
                </a>

                <a href="{{ route('client.visitors') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('client.visitors*') ? 'bg-[#0d0d0d] text-white' : 'text-[#333333] hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Visitors</span>
                </a>

                <a href="{{ route('client.invoices') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('client.invoices*') ? 'bg-[#0d0d0d] text-white' : 'text-[#333333] hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>My Invoices</span>
                </a>

                <a href="{{ route('client.embed-code') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('client.embed-code*') ? 'bg-[#0d0d0d] text-white' : 'text-[#333333] hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="code-2" class="w-4 h-4"></i>
                    <span>Embed Code</span>
                </a>

                <a href="{{ route('client.request-update.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('client.request-update*') ? 'bg-[#0d0d0d] text-white' : 'text-[#333333] hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    <span>Request Update</span>
                </a>

                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('profile*') || request()->routeIs('client.profile*') ? 'bg-[#0d0d0d] text-white' : 'text-[#333333] hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>My Profile</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 mt-auto">
                <div class="bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-2xl p-4 mb-4">
                    <p class="text-[10px] text-[#666666] font-semibold tracking-[0.65px] uppercase mb-1">Active Plan</p>
                    <p class="text-[#0d0d0d] font-semibold mb-3">{{ auth()->user()->plan }}</p>
                    <a href="https://wa.me/9779811144402" target="_blank" class="block w-full text-center py-2 bg-[#0d0d0d] text-white rounded-[9999px] text-xs font-medium hover:opacity-90 transition-opacity">Contact Support</a>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all hover:bg-[#f5f5f5] text-sm text-[#888888] hover:text-[#d45656]">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span class="font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 flex items-center justify-between px-4 lg:px-8 shrink-0 bg-white border-b border-[rgba(0,0,0,0.05)] sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-[#f5f5f5] transition-colors">
                        <i data-lucide="menu" class="w-5 h-5 text-[#0d0d0d]"></i>
                    </button>
                    <h2 class="text-lg font-semibold text-[#0d0d0d]">@yield('header', 'Dashboard')</h2>
                </div>
                <div class="flex items-center gap-3 pl-4 border-l border-[rgba(0,0,0,0.05)]">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-[#0d0d0d] leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-[#888888] font-medium mt-0.5">{{ auth()->user()->company_name ?? 'Client' }}</p>
                    </div>
                    <div class="w-9 h-9 bg-[#f5f5f5] rounded-full flex items-center justify-center overflow-hidden border border-[rgba(0,0,0,0.05)]">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=18E299&color=0d0d0d" alt="Avatar">
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-8 pt-6" id="main-content">
                <!-- Toast Notifications -->
                <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
                    @if(session('success'))
                        <div class="toast-notification animate-slide-in bg-brand-light border border-brand-deep/20 rounded-full px-4 py-3 text-brand-deep flex items-center gap-3 max-w-md shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
                            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                            <button onclick="closeToast(this)" class="ml-auto text-brand-deep/60 hover:text-brand-deep transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="toast-notification animate-slide-in bg-[#fef2f2] border border-[#d45656]/20 rounded-full px-4 py-3 text-[#d45656] flex items-center gap-3 max-w-md shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
                            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                            <button onclick="closeToast(this)" class="ml-auto text-[#d45656]/60 hover:text-[#d45656] transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @endif
                </div>

                @yield('content')
            </div>

            <!-- Loading Overlay -->
            <div id="loading-overlay" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-40 hidden flex items-center justify-center">
                <div class="bg-white rounded-2xl border border-[rgba(0,0,0,0.05)] px-6 py-4 flex items-center gap-3 shadow-[rgba(0,0,0,0.03)_0px_2px_4px]">
                    <div class="animate-spin w-5 h-5 border-2 border-[#0d0d0d] border-t-transparent rounded-full"></div>
                    <span class="text-[#0d0d0d] font-medium text-sm">Loading...</span>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function closeToast(button) {
            const toast = button.closest('.toast-notification');
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => { toast.remove(); }, 300);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast-notification');
            toasts.forEach((toast, index) => {
                setTimeout(() => {
                    if (toast.parentNode) {
                        closeToast(toast.querySelector('button'));
                    }
                }, 5000 + (index * 500));
            });
        });

        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    if (form.action.includes('request-update') || form.action.includes('pay')) {
                        showLoading();
                    }
                });
            });
        });

        window.showLoading = showLoading;
        window.hideLoading = hideLoading;
    </script>
</body>
</html>
