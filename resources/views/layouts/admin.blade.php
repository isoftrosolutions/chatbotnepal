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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F4F7FE] text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 bg-[#1B1B38] text-gray-400 flex flex-col shrink-0">
            <div class="p-8 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                    <i data-lucide="bot" class="text-white w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-white font-bold text-lg leading-none">ChatBot Nepal</h1>
                    <p class="text-[10px] text-gray-500 font-medium tracking-wider uppercase mt-1">B2B AI Solutions</p>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-2 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#4318FF] text-white shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('admin.usage') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.usage') ? 'bg-[#4318FF] text-white shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span class="font-medium">Analytics</span>
                </a>

                <a href="{{ route('admin.clients.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.clients.*') ? 'bg-[#4318FF] text-white shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="font-medium">Bot Manager</span>
                </a>

                <a href="{{ route('admin.knowledge-base') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.knowledge-base') ? 'bg-[#4318FF] text-white shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="database" class="w-5 h-5"></i>
                    <span class="font-medium">Knowledge Base</span>
                </a>

                <a href="{{ route('admin.embed-scripts') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.embed-scripts*') ? 'bg-[#4318FF] text-white shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="code-2" class="w-5 h-5"></i>
                    <span class="font-medium">Embed Scripts</span>
                </a>

                <a href="{{ route('admin.invoices.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.invoices.*') ? 'bg-[#4318FF] text-white shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span class="font-medium">Invoices</span>
                </a>

                <a href="{{ route('admin.settings') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.settings*') ? 'bg-[#4318FF] text-white shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)]' : 'hover:bg-white/5' }}">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span class="font-medium">Settings</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-6 mt-auto">
                <div class="bg-gradient-to-br from-[#4318FF] to-[#868CFF] rounded-2xl p-5 mb-6 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <p class="text-[10px] text-white/70 font-semibold uppercase tracking-wider mb-1">Current Tier: Enterprise</p>
                    <button class="w-full py-2.5 bg-white text-[#4318FF] rounded-xl text-xs font-bold shadow-lg hover:bg-gray-50 transition-colors">Upgrade Plan</button>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-4 px-4 py-2 rounded-xl transition-all hover:bg-white/5 text-sm">
                        <i data-lucide="user-circle" class="w-5 h-5"></i>
                        <span class="font-medium">User Profile</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-4 px-4 py-2 rounded-xl transition-all hover:bg-white/5 text-sm text-red-400">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-20 flex items-center justify-between px-8 shrink-0">
                <h2 class="text-2xl font-bold text-[#1B1B38]">@yield('header', 'Dashboard')</h2>

                <div class="flex items-center gap-6">
                    <!-- Search Bar -->
                    <div class="relative w-72">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" placeholder="Search analytics..." class="w-full pl-11 pr-4 py-2.5 bg-white rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-[#4318FF]/20 transition-all border-none">
                    </div>

                    <a href="{{ route('admin.clients.create') }}" class="bg-[#4318FF] text-white px-6 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-[0_10px_20px_-5px_rgba(67,24,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                        Add New Client
                    </a>

                    <div class="flex items-center gap-4">
                        <button class="relative w-10 h-10 flex items-center justify-center text-gray-400 hover:text-[#4318FF] transition-colors">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-[#F4F7FE]"></span>
                        </button>
                        <button class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-[#4318FF] transition-colors">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                        <div class="text-right">
                            <p class="text-sm font-bold text-[#1B1B38] leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-gray-400 font-medium mt-1">System Admin</p>
                        </div>
                        <div class="w-10 h-10 bg-[#FFB547]/20 rounded-xl flex items-center justify-center overflow-hidden">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=FFB547&color=fff" alt="Avatar">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-8 pt-2">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-2xl text-green-600 text-sm flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-600 text-sm flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
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
