@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-8 animate-fade-in">
    <div class="bg-white/90 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl">
        <div class="mb-6 text-center space-y-1">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xl mx-auto mb-3 shadow-lg shadow-blue-500/25">
                ⚡
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white font-display">Create Account</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Join DreamPC to configure and save custom builds</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 p-3 rounded-2xl text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/register" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none transition font-medium"
                    placeholder="Alex Morgan">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none transition font-medium"
                    placeholder="name@example.com">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none transition"
                    placeholder="••••••••">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none transition"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3.5 rounded-2xl text-xs transition shadow-lg shadow-blue-500/25 mt-2 hover:scale-[1.02] transform">
                Register Account
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
            Already have an account? <a href="/login" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Log in</a>
        </div>
    </div>
</div>
@endsection
