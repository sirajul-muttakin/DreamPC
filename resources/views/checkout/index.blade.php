@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-12 animate-fade-in">
    
    <!-- Page Header -->
    <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-500/25">
                📦
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white font-display tracking-tight">Secure Checkout & Delivery Scheduler</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Select fulfillment method, schedule delivery date, and confirm order</p>
            </div>
        </div>
        <a href="{{ route('cart.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-2xl text-xs font-bold transition text-center shadow-sm">
            ← Return to Cart
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 px-4 py-3 rounded-2xl text-xs">
            <span>⚠️ {{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf

        <!-- Form Fields -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Step 1: Fulfillment Selection -->
            <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-4 backdrop-blur-xl">
                <div class="flex items-center space-x-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center font-mono">1</span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white font-display">Select Fulfillment Method</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <!-- Delivery Option -->
                    <label class="relative flex flex-col p-5 bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-800 rounded-2xl cursor-pointer hover:border-blue-500 transition shadow-sm">
                        <input type="radio" name="fulfillment_type" value="delivery" checked 
                               onchange="toggleFulfillment('delivery')" class="hidden peer">
                        <div class="flex items-center justify-between peer-checked:text-blue-600 dark:peer-checked:text-blue-400">
                            <span class="text-3xl">🚚</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 font-mono">Express Delivery</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white mt-3 font-display">Home / Office Shipping</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Direct courier dispatch with full tracking to your specified address.</span>
                    </label>

                    <!-- Pickup Option -->
                    <label class="relative flex flex-col p-5 bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-800 rounded-2xl cursor-pointer hover:border-emerald-500 transition shadow-sm">
                        <input type="radio" name="fulfillment_type" value="pickup" 
                               onchange="toggleFulfillment('pickup')" class="hidden peer">
                        <div class="flex items-center justify-between peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400">
                            <span class="text-3xl">🏬</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 font-mono">In-Store Pickup</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white mt-3 font-display">Store Pickup Station</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Pick up directly from our hardware distribution warehouse (Free shipping).</span>
                    </label>
                </div>
            </div>

            <!-- Step 2: Date Scheduler & Contact Details -->
            <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-5 backdrop-blur-xl">
                <div class="flex items-center space-x-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center font-mono">2</span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white font-display">Schedule Date & Contact Info</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', Auth::user()->name ?? '') }}" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl px-4 py-3 outline-none focus:border-blue-500 font-medium" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number *</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+1 (555) 000-0000" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl px-4 py-3 outline-none focus:border-blue-500 font-mono" required>
                    </div>
                </div>

                <!-- Date Picker -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        📅 Scheduled <span id="schedule-type-label">Delivery</span> Date *
                    </label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d', strtotime('+1 day'))) }}" 
                           min="{{ date('Y-m-d') }}" 
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl px-4 py-3 outline-none focus:border-blue-500 font-mono" required>
                </div>

                <!-- Address Input -->
                <div id="address-container">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Shipping Address *</label>
                    <textarea name="address" rows="3" placeholder="Enter street address, city, state, zip code..." 
                              class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl p-3 outline-none focus:border-blue-500">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- Step 3: Order Confirmation Button -->
            <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 backdrop-blur-xl">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center font-mono">3</span>
                    <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">Ready to finalize your hardware order?</span>
                </div>
                <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs px-8 py-3.5 rounded-2xl transition shadow-lg shadow-blue-500/20 hover:scale-105 transform">
                    Place Order Now →
                </button>
            </div>

        </div>

        <!-- Order Summary Sidebar -->
        <div class="space-y-6">
            <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xl space-y-6 backdrop-blur-xl">
                <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 font-display">Order Summary</h2>

                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    @foreach($cart->items as $item)
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100 dark:border-slate-800/50">
                            <div class="truncate max-w-[70%]">
                                <span class="text-slate-800 dark:text-slate-200 font-bold truncate block">{{ $item->product->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">Qty: {{ $item->quantity }}</span>
                            </div>
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono font-black">${{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2 text-xs border-t border-slate-100 dark:border-slate-800 pt-4">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-mono text-slate-900 dark:text-slate-200 font-bold">${{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($appliedCoupon)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                            <span>Coupon ({{ $appliedCoupon['code'] }})</span>
                            <span class="font-mono">-${{ number_format($discount, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Est. Tax (5%)</span>
                        <span class="font-mono text-slate-900 dark:text-slate-200">${{ number_format($tax, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Shipping</span>
                        <span class="font-mono text-slate-900 dark:text-slate-200">${{ number_format($shipping, 2) }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between text-sm font-bold">
                        <span class="text-slate-900 dark:text-white">Total</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-mono text-lg font-black">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
function toggleFulfillment(type) {
    const addressContainer = document.getElementById('address-container');
    const label = document.getElementById('schedule-type-label');

    if (type === 'pickup') {
        addressContainer.classList.add('hidden');
        label.textContent = 'Pickup';
    } else {
        addressContainer.classList.remove('hidden');
        label.textContent = 'Delivery';
    }
}
</script>
@endsection
