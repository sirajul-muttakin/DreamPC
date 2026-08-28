<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DreamPC - Intelligent Hardware Marketplace') }}</title>
    
    <!-- Google Fonts & Tailwind CSS CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col antialiased selection:bg-blue-500 selection:text-white overflow-x-hidden">
    
    <!-- Responsive Navigation Bar -->
    @include('layouts.navigation')

    <!-- Flash Alert Messages -->
    @if (session('success') || session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if (session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center justify-between shadow-lg backdrop-blur-md">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">✕</button>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center justify-between shadow-lg backdrop-blur-md">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-200">✕</button>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Responsive Body Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950 text-slate-500 text-xs py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center sm:flex sm:justify-between sm:text-left">
            <p>&copy; {{ date('Y') }} DreamPC. All rights reserved.</p>
            <p class="mt-2 sm:mt-0">Intelligent Conversational Hardware Marketplace</p>
        </div>
    </footer>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-sm w-full px-4 sm:px-0"></div>

    <script>
        function updateNavCartCount(count) {
            const navBadges = [document.getElementById('nav-cart-count'), document.getElementById('mobile-nav-cart-count')];
            navBadges.forEach(badge => {
                if (!badge) return;
                badge.textContent = count;
                if (count > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
        }

        function showToast(message, type = 'success', action = null) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center justify-between p-4 rounded-xl shadow-2xl border backdrop-blur-xl transition-all duration-300 transform translate-y-2 opacity-0 text-xs font-medium ${
                type === 'success'
                    ? 'bg-slate-900/95 border-emerald-500/30 text-emerald-300 shadow-emerald-950/40'
                    : 'bg-slate-900/95 border-red-500/30 text-red-300 shadow-red-950/40'
            }`;

            let actionHtml = '';
            if (action && action.text && action.url) {
                actionHtml = `<a href="${action.url}" class="ml-3 px-2.5 py-1 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-200 border border-emerald-500/40 rounded-lg text-[11px] font-bold transition flex-shrink-0">${action.text}</a>`;
            }

            toast.innerHTML = `
                <div class="flex items-center space-x-2.5 min-w-0">
                    <span class="text-base flex-shrink-0">${type === 'success' ? '✓' : '⚠️'}</span>
                    <span class="truncate">${message}</span>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0 ml-2">
                    ${actionHtml}
                    <button onclick="this.closest('.pointer-events-auto').remove()" class="text-slate-400 hover:text-white text-sm ml-1 p-1">✕</button>
                </div>
            `;

            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            // Auto dismiss after 4 seconds
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>
