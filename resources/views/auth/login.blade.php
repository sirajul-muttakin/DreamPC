@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-8 animate-fade-in">
    <div class="bg-white/90 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl">
        <div class="mb-6 text-center space-y-1">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xl mx-auto mb-3 shadow-lg shadow-blue-500/25">
                ⚡
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white font-display">Welcome Back</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Sign in to your DreamPC hardware account</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 p-3 rounded-2xl text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none transition font-medium"
                    placeholder="name@example.com">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none transition"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center text-slate-600 dark:text-slate-400">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-blue-600 focus:ring-blue-500 mr-2">
                    Remember me
                </label>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3.5 rounded-2xl text-xs transition shadow-lg shadow-blue-500/25 mt-2 hover:scale-[1.02] transform">
                Sign In
            </button>
        </form>

        <!-- Quick Demo Credentials helper -->
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 text-center">
            <span class="text-[10px] text-slate-400 uppercase font-mono tracking-wider block mb-1">Admin Demo Credentials</span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">admin@dreampc.com / password</span>
        </div>

        <div class="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">
            Don't have an account? <a href="/register" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Register</a>
        </div>
    </div>
</div>
@endsection
