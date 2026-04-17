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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }

        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }

        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
    </style>
</head>
<body class="bg-[#F4F7FE] text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 bg-[#1B1B38] text-gray-400 flex flex-col shrink-0">
            <div class="p-8 flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="bot" class="text-white w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-white font-bold text-lg leading-none">ChatBot Nepal</h1>
                    <p class="text-[10px] text-indigo-300 font-medium tracking-wider uppercase mt-1">Client Portal</p>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-2 overflow-y-auto">
                <a href="{{ route('client.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('client.dashboard') ? 'bg-indigo-600 text-white shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('client.conversations') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('client.conversations*') ? 'bg-indigo-600 text-white shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="message-square" class="w-5 h-5"></i>
                    <span class="font-medium">Chat History</span>
                </a>

                <a href="{{ route('client.invoices') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('client.invoices*') ? 'bg-indigo-600 text-white shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    <span class="font-medium">My Invoices</span>
                </a>

                <a href="{{ route('client.embed-code') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('client.embed-code*') ? 'bg-indigo-600 text-white shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="code-2" class="w-5 h-5"></i>
                    <span class="font-medium">Embed Code</span>
                </a>

                <a href="{{ route('client.request-update.create') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('client.request-update*') ? 'bg-indigo-600 text-white shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                    <span class="font-medium">Request Update</span>
                </a>

                <a href="{{ route('profile.show') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('profile*') || request()->routeIs('client.profile*') ? 'bg-indigo-600 text-white shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span class="font-medium">My Profile</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-6 mt-auto">
                <div class="bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl p-5 mb-6 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <p class="text-[10px] text-white/70 font-semibold uppercase tracking-wider mb-1">Active Plan</p>
                    <p class="text-white font-bold mb-3">{{ auth()->user()->plan }}</p>
                    <a href="https://wa.me/9779811144402" target="_blank" class="block w-full text-center py-2 bg-white text-indigo-600 rounded-xl text-xs font-bold shadow-lg hover:bg-gray-50 transition-colors">Contact Support</a>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-4 px-4 py-3 rounded-xl transition-all hover:bg-white/5 text-sm text-red-400">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span class="font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-20 flex items-center justify-between px-8 shrink-0">
                <h2 class="text-2xl font-bold text-[#1B1B38]">@yield('header', 'Dashboard')</h2>

                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                        <div class="text-right">
                            <p class="text-sm font-bold text-[#1B1B38] leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-gray-400 font-medium mt-1">{{ auth()->user()->company_name ?? 'Client' }}</p>
                        </div>
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center overflow-hidden">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff" alt="Avatar">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-8 pt-2" id="main-content">
                <!-- Toast Notifications -->
                <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
                    @if(session('success'))
                        <div class="toast-notification animate-slide-in bg-[#E2FFF3] border border-[#05CD99]/20 rounded-2xl p-4 text-[#05CD99] shadow-lg flex items-center gap-3 max-w-md">
                            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                            <button onclick="closeToast(this)" class="ml-auto text-[#05CD99]/60 hover:text-[#05CD99] transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="toast-notification animate-slide-in bg-[#FEECEC] border border-[#EE5D50]/20 rounded-2xl p-4 text-[#EE5D50] shadow-lg flex items-center gap-3 max-w-md">
                            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                            <button onclick="closeToast(this)" class="ml-auto text-[#EE5D50]/60 hover:text-[#EE5D50] transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @endif
                </div>

                @yield('content')
            </div>

            <!-- Loading Overlay -->
            <div id="loading-overlay" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 hidden flex items-center justify-center">
                <div class="bg-white rounded-3xl p-8 shadow-2xl flex items-center gap-4">
                    <div class="animate-spin w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full"></div>
                    <span class="text-[#1B1B38] font-medium">Loading...</span>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Toast notification functions
        function closeToast(button) {
            const toast = button.closest('.toast-notification');
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }

        // Auto-hide toasts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast-notification');
            toasts.forEach((toast, index) => {
                setTimeout(() => {
                    if (toast.parentNode) {
                        closeToast(toast.querySelector('button'));
                    }
                }, 5000 + (index * 500)); // Stagger the auto-hide
            });
        });

        // Loading functions
        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        // Form submission loading
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    // Only show loading for forms that might take time
                    if (form.action.includes('request-update') || form.action.includes('pay')) {
                        showLoading();
                    }
                });
            });
        });

        // Global functions for pages to use
        window.showLoading = showLoading;
        window.hideLoading = hideLoading;
    </script>
</body>
</html>
