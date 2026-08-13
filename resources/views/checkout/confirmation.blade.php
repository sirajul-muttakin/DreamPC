@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-12 animate-fade-in">
    
    <!-- Success Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 border border-emerald-500/30 rounded-3xl p-8 sm:p-10 shadow-2xl text-center space-y-4 text-white">
        <div class="w-16 h-16 rounded-3xl bg-white/20 text-white text-3xl font-bold flex items-center justify-center mx-auto shadow-lg shadow-black/10 backdrop-blur-md">
            ✓
        </div>
        <div>
            <h1 class="text-3xl font-black font-display tracking-tight">Order Confirmed!</h1>
            <p class="text-xs sm:text-sm text-emerald-100 mt-1">Thank you for your order. We have received your hardware configuration!</p>
        </div>
        <div class="inline-block bg-black/20 backdrop-blur-md border border-white/20 px-5 py-2.5 rounded-2xl text-xs text-white font-mono">
            Order Reference ID: <strong class="text-white font-bold">#ORD-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong>
        </div>
    </div>

    <!-- Fulfillment Schedule Details -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 p-5 rounded-3xl space-y-1 shadow-md backdrop-blur-xl">
            <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Order Status</span>
            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider font-mono">{{ $order->status }}</span>
        </div>

        <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 p-5 rounded-3xl space-y-1 shadow-md backdrop-blur-xl">
            <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Fulfillment Method</span>
            <span class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider font-display">
                {{ $order->fulfillment_type === 'delivery' ? '🚚 Express Delivery' : '🏬 Store Pickup' }}
            </span>
        </div>

        <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 p-5 rounded-3xl space-y-1 shadow-md backdrop-blur-xl">
            <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Scheduled Date</span>
            <span class="text-sm font-bold text-blue-600 dark:text-blue-400 font-mono">
                {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') : 'Pending' }}
            </span>
        </div>
    </div>

    <!-- Purchased Hardware Components -->
    <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-4 backdrop-blur-xl">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 font-display">Purchased Hardware Components</h2>

        <div class="space-y-3">
            @foreach($order->items as $item)
                <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if(!empty($item->product->image_path))
                                <img src="{{ $item->product->image_path }}" alt="{{ $item->product->name ?? '' }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-lg">🖥️</span>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white font-display">{{ $item->product->name ?? 'Product Component' }}</h4>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                Qty: {{ $item->quantity }} × ${{ number_format($item->price_at_purchase, 2) }}
                            </div>
                        </div>
                    </div>
                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        ${{ number_format($item->quantity * $item->price_at_purchase, 2) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Actions Footer -->
    <div class="flex flex-col sm:flex-row justify-center gap-3 pt-4">
        <a href="{{ route('catalog.index') }}" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs px-6 py-3 rounded-2xl transition shadow-lg shadow-blue-500/20 text-center">
            Back to Catalog
        </a>
        <a href="{{ route('chat.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-bold text-xs px-6 py-3 rounded-2xl transition text-center shadow-sm">
            🤖 Ask AI for New Build
        </a>
    </div>

</div>
@endsection
