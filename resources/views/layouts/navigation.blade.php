<nav class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            
            <!-- Brand Logo -->
            <div class="flex items-center space-x-8">
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform">
                        ⚡
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-extrabold tracking-tight font-display bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-400 dark:via-indigo-300 dark:to-white bg-clip-text text-transparent">
                            DreamPC
                        </span>
                        <span class="text-[9px] uppercase tracking-widest text-slate-400 font-mono -mt-1">AI Hardware Lab</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <a href="/" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 transition flex items-center gap-1.5">
                        <span>🖥️</span>
                        <span>Catalog</span>
                    </a>
                    <a href="/build/summary" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 transition flex items-center gap-1.5">
                        <span>📊</span>
                        <span>Build Summary</span>
                    </a>
                    <a href="/chat" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition flex items-center gap-1.5">
                        <span class="animate-bounce">🤖</span>
                        <span>AI Assistant</span>
                    </a>
                    <a href="/orders" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 transition flex items-center gap-1.5">
                        <span>📜</span>
                        <span>Orders</span>
                    </a>
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition flex items-center gap-1.5">
                                <span>⚙️</span>
                                <span>Inventory CRUD</span>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Desktop Right Actions (Theme Switcher, Cart Badge & Auth) -->
            <div class="hidden md:flex items-center space-x-3">
                
                <!-- Light / Dark Mode Toggle Button -->
                <button type="button" onclick="toggleTheme()" 
                        class="p-2.5 rounded-2xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-white transition shadow-sm hover:scale-105 transform"
                        title="Toggle Light/Dark Theme">
                    <!-- Sun Icon (shown in dark mode) -->
                    <svg class="w-5 h-5 theme-sun-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon Icon (shown in light mode) -->
                    <svg class="w-5 h-5 theme-moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Cart Action Button with Live Counter Badge -->
                <a href="/cart" class="relative p-2.5 rounded-2xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition shadow-sm hover:scale-105 transform flex items-center gap-1">
                    <span class="text-base">🛒</span>
                    <span id="nav-cart-badge" class="ml-1 text-xs font-bold font-mono bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded-full">
                        {{ \App\Models\CartItem::whereHas('cart', function($q) { 
                            if(auth()->check()) {
                                $q->where('user_id', auth()->id());
                            } else {
                                $q->where('session_id', session('cart_session_id', ''));
                            }
                        })->sum('quantity') ?: 0 }}
                    </span>
                </a>

                @auth
                    <div class="flex items-center space-x-3 pl-2 border-l border-slate-200 dark:border-slate-800">
                        <div class="flex flex-col text-right">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</span>
                            @if(Auth::user()->role === 'admin')
                                <span class="text-[9px] text-indigo-500 font-mono font-bold uppercase tracking-wider">Admin</span>
                            @else
                                <span class="text-[9px] text-slate-400 font-mono">Member</span>
                            @endif
                        </div>
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-500/20 transition text-xs font-semibold">
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center space-x-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                        <a href="/login" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-white transition">Log in</a>
                        <a href="/register" class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white transition shadow-lg shadow-blue-500/20 hover:scale-105 transform">
                            Register
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Right Controls (Theme Toggle & Hamburger) -->
            <div class="flex items-center space-x-2 md:hidden">
                <button type="button" onclick="toggleTheme()" 
                        class="p-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300">
                    <svg class="w-5 h-5 theme-sun-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="w-5 h-5 theme-moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <a href="/cart" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 flex items-center">
                    <span>🛒</span>
                </a>

                <button type="button" id="mobile-menu-toggle" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="hamburger-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Slide-Down Menu Drawer -->
    <div id="mobile-menu" class="hidden md:hidden border-b border-slate-200/80 dark:border-slate-800/80 bg-white/95 dark:bg-slate-950/95 backdrop-blur-2xl px-4 pt-2 pb-6 space-y-4">
        <div class="space-y-1 pt-2">
            <a href="/" class="block px-3 py-2.5 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900 transition">🖥️ Catalog</a>
            <a href="/build/summary" class="block px-3 py-2.5 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900 transition">📊 Build Summary</a>
            <a href="/chat" class="block px-3 py-2.5 rounded-xl text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition">🤖 AI Assistant</a>
            <a href="/orders" class="block px-3 py-2.5 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900 transition">📜 Orders</a>
            @auth
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('products.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition">⚙️ Inventory CRUD</a>
                @endif
            @endauth
        </div>

        <div class="border-t border-slate-200 dark:border-slate-800 pt-4 space-y-3">
            @auth
                <div class="px-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ Auth::user()->email }}</div>
                    </div>
                    @if(Auth::user()->role === 'admin')
                        <span class="text-[10px] bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 px-2.5 py-0.5 rounded-full font-bold uppercase font-mono">Admin</span>
                    @endif
                </div>
                <form method="POST" action="/logout" class="block">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2.5 text-sm font-bold text-red-600 dark:text-red-400 hover:bg-red-500/10 rounded-xl transition">
                        Logout
                    </button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-3 px-3">
                    <a href="/login" class="text-center text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 py-3 rounded-xl transition shadow-sm">Log in</a>
                    <a href="/register" class="text-center text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 py-3 rounded-xl transition shadow-lg shadow-blue-500/20">Register</a>
                </div>
            @endauth
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');

        if (toggleBtn && mobileMenu) {
            toggleBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                hamburgerIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });
        }
    });
</script>
