<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DreamPC - Intelligent Hardware Marketplace') }}</title>
    
    <!-- Google Fonts & Tailwind CSS CDN with dark mode config -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script>
        // Check theme immediately to prevent FOUC
        if (localStorage.getItem('dreampc_theme') === 'light' || (!('dreampc_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        }
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    keyframes: {
                        'pulse-slow': {
                            '0%, 100%': { opacity: '0.4', transform: 'scale(1)' },
                            '50%': { opacity: '0.8', transform: 'scale(1.05)' },
                        },
                        'float': {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-6px)' },
                        },
                        'shimmer': {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' },
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse-slow 6s ease-in-out infinite',
                        'float': 'float 4s ease-in-out infinite',
                        'shimmer': 'shimmer 2.5s infinite linear',
                    }
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.6);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.9);
        }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col antialiased selection:bg-blue-500 selection:text-white overflow-x-hidden transition-colors duration-300 relative min-h-screen">
    
    <!-- Ambient Background Gradient Glow Orbs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-500/10 dark:bg-blue-600/15 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute top-1/3 -right-40 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-600/15 rounded-full blur-3xl animate-pulse-slow [animation-delay:2s]"></div>
        <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-purple-500/10 dark:bg-purple-600/10 rounded-full blur-3xl animate-pulse-slow [animation-delay:4s]"></div>
    </div>

    <!-- Responsive Navigation Bar -->
    @include('layouts.navigation')

    <!-- Flash Alert Messages -->
    @if (session('success') || session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full relative z-10">
            @if (session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-2xl text-xs sm:text-sm flex items-center justify-between shadow-xl backdrop-blur-xl animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <span>✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-700 dark:text-emerald-300 hover:opacity-75">✕</button>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 px-4 py-3 rounded-2xl text-xs sm:text-sm flex items-center justify-between shadow-xl backdrop-blur-xl animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-700 dark:text-red-300 hover:opacity-75">✕</button>
                </div>
            @endif
        </div>
    @endif

    <!-- Global Floating Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col space-y-2 pointer-events-none"></div>

    <!-- Main Responsive Body Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200/80 dark:border-slate-800/80 bg-white/70 dark:bg-slate-950/70 backdrop-blur-md text-slate-500 dark:text-slate-500 text-xs py-6 mt-auto relative z-10 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 text-center sm:flex sm:justify-between sm:text-left items-center">
            <div class="flex items-center justify-center sm:justify-start space-x-2">
                <span class="font-bold text-slate-700 dark:text-slate-300 font-display">⚡ DreamPC</span>
                <span>&copy; {{ date('Y') }} All rights reserved.</span>
            </div>
            <p class="mt-2 sm:mt-0 text-slate-400">Intelligent PC Hardware Marketplace & AI Compatibility Engine</p>
        </div>
    </footer>

    <!-- Global Toast & Theme Helper Scripts -->
    <script>
        // Global Toast Trigger
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            
            toast.className = `pointer-events-auto flex items-center space-x-3 px-4 py-3 rounded-2xl shadow-xl backdrop-blur-xl border text-xs font-semibold transform transition-all duration-300 translate-y-4 opacity-0 ${
                isSuccess 
                ? 'bg-slate-900/90 text-emerald-400 border-emerald-500/40 shadow-emerald-500/10' 
                : 'bg-slate-900/90 text-red-400 border-red-500/40 shadow-red-500/10'
            }`;

            toast.innerHTML = `
                <span class="text-sm">${isSuccess ? '✓' : '⚠️'}</span>
                <span class="text-slate-200">${message}</span>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-4', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };

        // Theme Toggle Function
        window.toggleTheme = function() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('dreampc_theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                localStorage.setItem('dreampc_theme', 'dark');
            }
            updateThemeToggleIcons();
        };

        function updateThemeToggleIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            const sunIcons = document.querySelectorAll('.theme-sun-icon');
            const moonIcons = document.querySelectorAll('.theme-moon-icon');

            sunIcons.forEach(el => {
                if (isDark) el.classList.remove('hidden');
                else el.classList.add('hidden');
            });

            moonIcons.forEach(el => {
                if (isDark) el.classList.add('hidden');
                else el.classList.remove('hidden');
            });
        }

        document.addEventListener('DOMContentLoaded', updateThemeToggleIcons);
    </script>
</body>
</html>
