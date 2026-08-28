<nav class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-8">
                <a href="/" class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/30">
                        ⚡
                    </div>
                    <span class="text-xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-300 to-white">
                        DreamPC
                    </span>
                </a>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="/" class="text-sm font-medium text-slate-300 hover:text-white transition">Catalog</a>
                    <a href="/build/summary" class="text-sm font-medium text-slate-300 hover:text-white transition flex items-center gap-1">
                        <span>📊</span>
                        <span>Build Summary</span>
                    </a>
                    <a href="/chat" class="text-sm font-medium text-blue-400 hover:text-blue-300 transition flex items-center gap-1.5">
                        <span>🤖</span>
                        <span>AI Assistant</span>
                    </a>
                    <a href="/cart" class="text-sm font-medium text-emerald-400 hover:text-emerald-300 transition flex items-center gap-1.5">
                        <span>🛒</span>
                        <span>Cart</span>
                        <span id="nav-cart-count" class="bg-emerald-500/20 text-emerald-300 text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-emerald-500/30 {{ ($navCartCount ?? 0) > 0 ? '' : 'hidden' }}">
                            {{ $navCartCount ?? 0 }}
                        </span>
                    </a>
                    <a href="/orders" class="text-sm font-medium text-slate-300 hover:text-white transition flex items-center gap-1">
                        <span>📜</span>
                        <span>Orders</span>
                    </a>
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('products.index') }}" class="text-sm font-medium text-indigo-400 hover:text-indigo-300 transition">Inventory CRUD</a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Desktop Auth Controls -->
            <div class="hidden md:flex items-center space-x-4">
                @auth
                    <div class="flex items-center space-x-3">
                        <span class="text-xs text-slate-400">
                            Hi, <strong class="text-slate-200">{{ Auth::user()->name }}</strong>
                        </span>
                        @if(Auth::user()->role === 'admin')
                            <span class="text-[10px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider">
                                Admin
                            </span>
                        @endif
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-slate-400 hover:text-red-400 transition font-medium">
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <a href="/login" class="text-sm text-slate-300 hover:text-white transition font-medium">Log in</a>
                    <a href="/register" class="text-sm bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium transition shadow-lg shadow-blue-600/20">
                        Register
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger Toggle Button -->
            <div class="flex md:hidden">
                <button type="button" id="mobile-menu-toggle" class="text-slate-400 hover:text-white focus:outline-none p-2 rounded-lg hover:bg-slate-800 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="hamburger-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Slide-Down Menu Drawer -->
    <div id="mobile-menu" class="hidden md:hidden border-b border-slate-800 bg-slate-950/95 backdrop-blur-2xl px-4 pt-2 pb-6 space-y-4">
        <div class="space-y-1 pt-2">
            <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-white transition">Catalog</a>
            <a href="/build/summary" class="block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-white transition">📊 Build Summary</a>
            <a href="/chat" class="block px-3 py-2 rounded-md text-base font-medium text-blue-400 hover:bg-blue-950/40 transition">🤖 AI Assistant</a>
            <a href="/cart" class="block px-3 py-2 rounded-md text-base font-medium text-emerald-400 hover:bg-emerald-950/40 transition flex items-center justify-between">
                <span>🛒 Cart</span>
                <span id="mobile-nav-cart-count" class="bg-emerald-500/20 text-emerald-300 text-xs font-bold px-2 py-0.5 rounded-full border border-emerald-500/30 {{ ($navCartCount ?? 0) > 0 ? '' : 'hidden' }}">
                    {{ $navCartCount ?? 0 }}
                </span>
            </a>
            <a href="/orders" class="block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-white transition">📜 Orders</a>
            @auth
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-400 hover:bg-indigo-950/50 transition">Inventory CRUD</a>
                @endif
            @endauth
        </div>

        <div class="border-t border-slate-800 pt-4 space-y-3">
            @auth
                <div class="px-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
                    </div>
                    @if(Auth::user()->role === 'admin')
                        <span class="text-[10px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-0.5 rounded-full font-semibold uppercase">Admin</span>
                    @endif
                </div>
                <form method="POST" action="/logout" class="block">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-md transition">
                        Logout
                    </button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-3 px-3">
                    <a href="/login" class="text-center text-sm font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 py-2.5 rounded-lg transition">Log in</a>
                    <a href="/register" class="text-center text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 py-2.5 rounded-lg transition shadow-md shadow-blue-600/20">Register</a>
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
