@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-12 animate-fade-in">
    
    <!-- Page Header -->
    <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-500/25">
                📜
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white font-display tracking-tight">Order History & Build Archive</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track fulfillment statuses and inspect archived technical spec snapshots</p>
            </div>
        </div>
        <a href="{{ route('catalog.index') }}" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-5 py-2.5 rounded-2xl text-xs font-bold transition shadow-lg shadow-blue-500/20 text-center">
            Browse Components
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-12 text-center space-y-4 shadow-xl">
            <span class="text-5xl opacity-40">📜</span>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white font-display">No Orders Found</h2>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">You have not placed any orders yet. Visit our catalog to configure and purchase your first hardware build!</p>
            <div class="pt-2 flex justify-center">
                <a href="{{ route('catalog.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-lg shadow-blue-500/20">
                    Explore Catalog
                </a>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xl space-y-4 p-6 sm:p-7 backdrop-blur-xl">
                    
                    <!-- Order Summary Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center space-x-4">
                            <div>
                                <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Order Reference</span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">#ORD-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Date Placed</span>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $order->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Fulfillment</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $order->fulfillment_type === 'delivery' ? '🚚 Express Delivery' : '🏬 Store Pickup' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Total Amount</span>
                                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            
                            <!-- Reorder Button -->
                            <form method="POST" action="{{ route('orders.reorder', $order->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-md shadow-blue-500/20 flex items-center space-x-1.5 hover:scale-105 transform">
                                    <span>🔄</span>
                                    <span>Reorder</span>
                                </button>
                            </form>

                            <!-- Expand/Collapse Accordion -->
                            <button type="button" onclick="toggleOrderDetails({{ $order->id }})" 
                                    class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-sm">
                                <span id="toggle-text-{{ $order->id }}">View Components ▼</span>
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Component Breakdown -->
                    <div id="order-details-{{ $order->id }}" class="hidden space-y-4 pt-2">
                        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider font-mono">Archived PC Build Components:</h3>

                        <div class="grid grid-cols-1 gap-3">
                            @foreach($order->items as $item)
                                <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-base">
                                                🖥️
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-900 dark:text-white font-display">
                                                    {{ $item->product->name ?? 'Deleted / Custom Hardware Component' }}
                                                </h4>
                                                <span class="text-[10px] text-slate-400 font-mono">
                                                    Quantity: {{ $item->quantity }} × Price: ${{ number_format($item->price_at_purchase, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                            ${{ number_format($item->quantity * $item->price_at_purchase, 2) }}
                                        </span>
                                    </div>

                                    <!-- Historical Specifications Snapshot -->
                                    @if(!empty($item->spec_snapshot_json))
                                        <div class="bg-white dark:bg-slate-900/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                                            <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block mb-2 font-mono">Snapshot at Purchase Time</span>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                @foreach($item->spec_snapshot_json as $key => $val)
                                                    <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/50 px-2.5 py-1.5 rounded-lg text-[10px] flex flex-col">
                                                        <span class="text-slate-400 uppercase font-medium text-[9px] font-mono">{{ str_replace('_', ' ', $key) }}</span>
                                                        <strong class="text-slate-800 dark:text-slate-200 mt-0.5">{{ $val }}</strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
function toggleOrderDetails(orderId) {
    const detailsDiv = document.getElementById(`order-details-${orderId}`);
    const toggleText = document.getElementById(`toggle-text-${orderId}`);

    if (detailsDiv.classList.contains('hidden')) {
        detailsDiv.classList.remove('hidden');
        toggleText.textContent = 'Hide Components ▲';
    } else {
        detailsDiv.classList.add('hidden');
        toggleText.textContent = 'View Components ▼';
    }
}
</script>
@endsection
