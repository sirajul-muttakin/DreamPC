<div class="mt-4 bg-slate-950/90 border border-slate-800 rounded-xl p-4 shadow-xl space-y-4">
    <!-- Header with Compatibility Badge & Total Price -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <div class="flex items-center space-x-2">
<<<<<<< Updated upstream
            <span class="text-xs font-semibold text-slate-300">Complete Hardware Build Spec</span>
            @if($isCompatible ?? true)
                <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 uppercase tracking-wider">
=======
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-display">Complete Build Spec</span>
            @if($isCompatible ?? true)
                <span class="bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1 uppercase tracking-wider font-mono">
>>>>>>> Stashed changes
                    <span>✓</span> 100% Compatible ({{ count($products) }} Parts)
                </span>
            @else
                <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 uppercase tracking-wider">
                    <span>⚠️</span> Compatibility Warning
                </span>
            @endif
        </div>
        <div class="text-right">
<<<<<<< Updated upstream
            <span class="text-[10px] text-slate-500 block">Est. Build Total</span>
            <span class="text-sm font-bold text-emerald-400 font-mono">${{ number_format($totalPrice ?? 0, 2) }}</span>
=======
            <span class="text-[9px] text-slate-400 dark:text-slate-500 block font-semibold uppercase">Est. Build Total</span>
            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">${{ number_format($totalPrice ?? 0, 2) }}</span>
>>>>>>> Stashed changes
        </div>
    </div>

    <!-- Product Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        @foreach($products as $product)
            <div class="bg-slate-900/80 border border-slate-800/80 hover:border-blue-500/40 p-3 rounded-lg flex items-center space-x-3 transition">
                <div class="w-12 h-12 rounded-md bg-slate-950 border border-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                    @if(!empty($product->image_path))
                        <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-lg">🖥️</span>
                    @endif
                </div>
                <div class="flex-grow min-w-0">
                    <div class="flex items-center space-x-1.5">
                        <span class="text-[9px] bg-slate-800 text-slate-300 border border-slate-700/60 px-1.5 py-0.5 rounded font-mono uppercase">
                            {{ $product->category->name ?? 'Part' }}
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $product->brand }}</span>
                    </div>
<<<<<<< Updated upstream
                    <h4 class="text-xs font-semibold text-slate-200 truncate mt-0.5">{{ $product->name }}</h4>
                    <div class="text-xs font-bold text-emerald-400 font-mono mt-0.5">${{ number_format($product->price, 2) }}</div>
=======
                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate mt-0.5">{{ $product->name }}</h4>
                    <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">${{ number_format($product->price, 2) }}</div>
>>>>>>> Stashed changes
                </div>
            </div>
        @endforeach
    </div>

    @if(!empty($incompatibilities))
        <div class="bg-amber-500/10 border border-amber-500/20 p-2.5 rounded-lg text-xs text-amber-300 space-y-1">
            @foreach($incompatibilities as $warning)
                <p>• {{ $warning }}</p>
            @endforeach
        </div>
    @endif

    <!-- Add Build to Cart Button -->
    <div class="pt-2 border-t border-slate-800/80 flex justify-end">
        <form method="POST" action="{{ route('cart.batch-add') }}" class="w-full sm:w-auto">
            @csrf
            @foreach($products as $product)
                <input type="hidden" name="product_ids[]" value="{{ $product->id }}">
            @endforeach
            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold px-4 py-2.5 rounded-lg transition shadow-md shadow-blue-500/20 flex items-center justify-center space-x-2">
                <span>🛒</span>
                <span>Add Entire Build to Cart</span>
            </button>
        </form>
    </div>
</div>
