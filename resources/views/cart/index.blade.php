@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-12 animate-fade-in">
    
    <!-- Page Header -->
    <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white text-2xl shadow-lg shadow-emerald-500/25">
                🛒
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white font-display tracking-tight">Interactive Shopping Cart</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Review component specs, verify live compatibility, or swap alternatives</p>
            </div>
        </div>
        <a href="{{ route('catalog.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-2xl text-xs font-bold transition text-center shadow-sm">
            Continue Shopping
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-2xl text-xs">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 px-4 py-3 rounded-2xl text-xs">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    @if($cart->items->isEmpty())
        <div class="bg-white/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-12 text-center space-y-4 shadow-xl">
            <span class="text-5xl opacity-40">🛒</span>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white font-display">Your Shopping Cart is Empty</h2>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Explore our catalog or ask our AI Hardware Assistant to assemble your complete custom PC build!</p>
            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('catalog.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-lg shadow-blue-500/20">
                    Explore Catalog
                </a>
                <a href="{{ route('chat.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-xl text-xs font-bold transition">
                    🤖 Talk to AI Assistant
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Compatibility Banner -->
                <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl flex items-center justify-between shadow-sm backdrop-blur-xl">
                    <div class="flex items-center space-x-3">
                        <span class="text-xl">⚙️</span>
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 font-display">Cart Build Compatibility</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Automated socket, RAM generation & PSU headroom check</p>
                        </div>
                    </div>
                    @if($compatCheck['is_compatible'])
                        <span class="bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider font-mono">
                            ✓ 100% Compatible
                        </span>
                    @else
                        <span class="bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider font-mono">
                            ⚠️ Incompatibility Detected
                        </span>
                    @endif
                </div>

                @if(!$compatCheck['is_compatible'] && !empty($compatCheck['incompatibilities']))
                    <div class="bg-amber-500/10 border border-amber-500/30 p-4 rounded-2xl text-xs text-amber-800 dark:text-amber-300 space-y-1">
                        @foreach($compatCheck['incompatibilities'] as $warning)
                            <p>• {{ $warning }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Cart Items List -->
                <div class="space-y-3">
                    @foreach($cart->items as $item)
                        <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 hover:border-blue-500/40 dark:hover:border-blue-500/40 p-5 rounded-3xl transition space-y-3 shadow-md backdrop-blur-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if($item->product->image_path)
                                        <img src="{{ $item->product->image_path }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-2xl">🖥️</span>
                                    @endif
                                </div>

                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/50 px-2 py-0.5 rounded-md font-mono uppercase font-bold">
                                            {{ $item->product->category->name ?? 'Part' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $item->product->brand }}</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate mt-1 font-display">{{ $item->product->name }}</h3>
                                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-black mt-0.5 font-mono">${{ number_format($item->unit_price, 2) }}</div>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-1.5 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-1 shadow-inner">
                                    <button type="button" onclick="updateCartQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" 
                                            class="w-7 h-7 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-xs font-bold transition shadow-sm">-</button>
                                    <span id="quantity-{{ $item->id }}" class="w-8 text-center text-xs font-bold text-slate-900 dark:text-white font-mono">{{ $item->quantity }}</span>
                                    <button type="button" onclick="updateCartQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" 
                                            class="w-7 h-7 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-xs font-bold transition shadow-sm">+</button>
                                </div>

                                <div class="text-right flex flex-col items-end space-y-2">
                                    <span id="subtotal-{{ $item->id }}" class="text-sm font-black text-slate-900 dark:text-white font-mono">
                                        ${{ number_format($item->quantity * $item->unit_price, 2) }}
                                    </span>
                                    <form method="POST" action="{{ route('cart.destroy', $item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[11px] text-slate-400 hover:text-red-500 transition font-bold">Remove</button>
                                    </form>
                                </div>
                            </div>

                            @if(!empty($alternativesMap[$item->id]))
                                <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                                    <span class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold">🔄 Swap with compatible alternative:</span>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach($alternativesMap[$item->id] as $altProduct)
                                            <form method="POST" action="{{ route('cart.swap', $item->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="new_product_id" value="{{ $altProduct['id'] }}">
                                                <button type="submit" class="bg-slate-50 dark:bg-slate-950 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 px-2.5 py-1 rounded-xl text-[10px] font-mono transition shadow-sm">
                                                    {{ Str::limit($altProduct['name'], 20) }} (${{ number_format($altProduct['price'], 2) }})
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary Financial Card -->
            <div class="space-y-6">
                <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-6 backdrop-blur-xl">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 font-display">Order Financial Summary</h2>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span id="summary-subtotal" class="font-mono text-slate-900 dark:text-slate-200 font-bold">${{ number_format($subtotal, 2) }}</span>
                        </div>

                        @if($appliedCoupon)
                            <div id="coupon-section" class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 font-semibold bg-emerald-500/10 border border-emerald-500/20 px-3 py-2 rounded-xl">
                                <div class="flex items-center space-x-1.5">
                                    <span>🎟️ {{ $appliedCoupon['code'] }}</span>
                                    <span class="text-[10px] bg-emerald-500/20 px-1.5 py-0.5 rounded font-mono font-bold">
                                        {{ $appliedCoupon['discount_type'] === 'percent' ? $appliedCoupon['value'] . '%' : '$' . $appliedCoupon['value'] }} OFF
                                    </span>
                                </div>
                                <span id="summary-discount" class="font-mono font-bold">-${{ number_format($discount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>After Discount</span>
                                <span id="summary-discounted-subtotal" class="font-mono text-slate-900 dark:text-slate-200 font-bold">${{ number_format($discountedSubtotal, 2) }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Est. Tax (5%)</span>
                            <span id="summary-tax" class="font-mono text-slate-900 dark:text-slate-200">${{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Shipping ($15 base + $2/item)</span>
                            <span id="summary-shipping" class="font-mono text-slate-900 dark:text-slate-200">${{ number_format($shipping, 2) }}</span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between text-sm font-bold">
                            <span class="text-slate-900 dark:text-white">Total</span>
                            <span id="summary-total" class="text-emerald-600 dark:text-emerald-400 font-mono text-lg font-black">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Coupon Code Input -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        @if($appliedCoupon)
                            <form method="POST" action="{{ route('coupon.remove') }}">
                                @csrf
                                <button type="submit" class="w-full bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 font-bold text-xs py-2.5 rounded-2xl transition">
                                    Remove Coupon ({{ $appliedCoupon['code'] }})
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('coupon.apply') }}" class="flex items-center space-x-2">
                                @csrf
                                <input type="text" name="code" placeholder="Coupon Code (e.g. SAVE10)" 
                                       class="flex-grow bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white uppercase rounded-xl px-3 py-2.5 outline-none focus:border-blue-500 transition font-mono" required>
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-500/20 flex-shrink-0">
                                    Apply
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="space-y-2.5 pt-2">
                        <a href="{{ route('checkout.index') }}" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs py-3.5 rounded-2xl transition shadow-lg shadow-emerald-500/20 text-center block hover:scale-[1.02] transform">
                            Proceed to Checkout →
                        </a>
                        <a href="{{ route('build.summary') }}" class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-bold text-xs py-3 rounded-2xl transition text-center block shadow-sm">
                            📊 View Build Summary Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
async function updateCartQuantity(itemId, quantity) {
    if (quantity < 0) return;

    try {
        const response = await fetch(`/cart/items/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity })
        });

        const data = await response.json();
        if (data.status !== 'success') return;

        if (quantity === 0) {
            window.location.reload();
            return;
        }

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        };

        setText(`quantity-${itemId}`, data.item_quantity);
        setText(`subtotal-${itemId}`, data.item_subtotal);
        setText('summary-subtotal', data.cart_subtotal);
        setText('summary-tax', data.cart_tax);
        setText('summary-shipping', data.cart_shipping);
        setText('summary-total', data.cart_total);

        if (data.discount !== undefined) {
            setText('summary-discount', data.discount);
            setText('summary-discounted-subtotal', data.discounted_subtotal);
        }

        // Update nav cart badge
        const badge = document.getElementById('nav-cart-badge');
        if (badge) {
            const quantities = Array.from(document.querySelectorAll('[id^="quantity-"]'))
                .map(el => parseInt(el.textContent.trim()) || 0);
            badge.textContent = quantities.reduce((a, b) => a + b, 0);
        }

        window.showToast('Cart updated', 'success');
    } catch (e) {
        console.error('Error updating cart quantity:', e);
    }
}
</script>
@endsection