<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - ChatBot Nepal</title>
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
    </style>
</head>
<body class="bg-[#fafafa] text-[#0d0d0d]">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="sidebarOpen = false" class="fixed inset-0 bg-black/30 z-40 lg:hidden"></div>

        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-[rgba(0,0,0,0.05)] text-[#666666] flex flex-col shrink-0 fixed inset-y-0 left-0 z-50 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto transition-transform duration-200 ease-in-out" :class="{ 'translate-x-0': sidebarOpen }">
            <div class="p-4 sm:p-6 flex items-center gap-3">
                <div class="w-9 h-9 bg-[#0d0d0d] rounded-[9999px] flex items-center justify-center">
                    <i data-lucide="bot" class="text-white w-5 h-5"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-[#0d0d0d] font-semibold text-base leading-none">ChatBot Nepal</h1>
                    <p class="text-[10px] text-[#666666] font-medium tracking-[0.65px] uppercase mt-1">Admin Console</p>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg hover:bg-[#f5f5f5] transition-colors">
                    <i data-lucide="x" class="w-4 h-4 text-[#666666]"></i>
                </button>
            </div>

            <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-[#0d0d0d] text-white' : 'hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.usage') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('admin.usage') ? 'bg-[#0d0d0d] text-white' : 'hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Analytics</span>
                </a>

                <a href="{{ route('admin.clients.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('admin.clients.*') ? 'bg-[#0d0d0d] text-white' : 'hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Bot Manager</span>
                </a>

                <a href="{{ route('admin.knowledge-base') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('admin.knowledge-base') ? 'bg-[#0d0d0d] text-white' : 'hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="database" class="w-4 h-4"></i>
                    <span>Knowledge Base</span>
                </a>

                <a href="{{ route('admin.embed-scripts') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('admin.embed-scripts*') ? 'bg-[#0d0d0d] text-white' : 'hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="code-2" class="w-4 h-4"></i>
                    <span>Embed Scripts</span>
                </a>

                <a href="{{ route('admin.invoices.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('admin.invoices.*') ? 'bg-[#0d0d0d] text-white' : 'hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Invoices</span>
                </a>

                <a href="{{ route('admin.settings') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all text-sm font-medium {{ request()->routeIs('admin.settings*') ? 'bg-[#0d0d0d] text-white' : 'hover:bg-[#f5f5f5]' }}">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Settings</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 mt-auto">
                <div class="bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-2xl p-4 mb-4">
                    <p class="text-[10px] text-[#666666] font-semibold tracking-[0.65px] uppercase mb-1">Current Tier</p>
                    <p class="text-[#0d0d0d] font-semibold mb-3">Enterprise</p>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile.show') }}" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all hover:bg-[#f5f5f5] text-sm font-medium">
                        <i data-lucide="user-circle" class="w-4 h-4"></i>
                        <span>User Profile</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[9999px] transition-all hover:bg-[#f5f5f5] text-sm font-medium text-[#888888] hover:text-[#d45656]">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-14 sm:h-16 flex items-center justify-between px-3 sm:px-4 lg:px-8 shrink-0 bg-white border-b border-[rgba(0,0,0,0.05)] sticky top-0 z-30">
                <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                    <!-- Mobile Menu Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-[#f5f5f5] transition-colors flex-shrink-0">
                        <i data-lucide="menu" class="w-5 h-5 text-[#0d0d0d]"></i>
                    </button>
                    <h2 class="text-base sm:text-lg font-semibold text-[#0d0d0d] truncate">@yield('header', 'Dashboard')</h2>
                </div>

                <div class="flex items-center gap-2 sm:gap-4 pl-2 sm:pl-4 border-l border-[rgba(0,0,0,0.05)] flex-shrink-0">
                    <!-- Search Bar -->
                    <div class="relative hidden lg:block">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#888888]"></i>
                        <input type="text" placeholder="Search..." class="w-52 xl:w-64 pl-10 pr-4 py-2 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-[9999px] text-sm focus:outline-none focus:border-[#18E299] focus:ring-1 focus:ring-[#18E299] transition-all" />
                    </div>

                    <a href="{{ route('admin.clients.create') }}" class="bg-[#0d0d0d] text-white px-3 sm:px-5 py-2 rounded-[9999px] text-sm font-medium flex items-center gap-2 hover:opacity-90 transition-opacity shadow-[rgba(0,0,0,0.06)_0px_1px_2px]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Add Client</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <button class="relative w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-[#888888] hover:text-[#0d0d0d] transition-colors">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#d45656] rounded-full border-2 border-white"></span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-3 border-l border-[rgba(0,0,0,0.05)]">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium text-[#0d0d0d] leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-[#888888] font-medium mt-0.5">Admin</p>
                        </div>
                        <div class="w-8 h-8 sm:w-9 sm:h-9 bg-[#f5f5f5] rounded-full flex items-center justify-center overflow-hidden border border-[rgba(0,0,0,0.05)]">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=18E299&color=0d0d0d" alt="Avatar">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-3 sm:p-4 lg:p-8 pt-4 sm:pt-6">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-brand-light border border-brand-deep/20 rounded-full text-brand-deep text-sm flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 p-4 bg-[#fef2f2] border border-[#d45656]/20 rounded-full text-[#d45656] text-sm flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
