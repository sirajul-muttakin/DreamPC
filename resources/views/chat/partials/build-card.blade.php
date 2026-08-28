<div class="mt-4 bg-slate-950/90 border border-slate-800 rounded-xl p-4 shadow-xl space-y-4">
    <!-- Header with Compatibility Badge & Total Price -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold text-slate-300">Suggested Hardware Spec</span>
            @if($isCompatible ?? true)
                <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 uppercase tracking-wider">
                    <span>✓</span> 100% Compatible
                </span>
            @else
                <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 uppercase tracking-wider">
                    <span>⚠️</span> Compatibility Warning
                </span>
            @endif
        </div>
        <div class="text-right">
            <span class="text-[10px] text-slate-500 block">Est. Build Total</span>
            <span class="text-sm font-bold text-emerald-400">${{ number_format($totalPrice ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- Product Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                    <h4 class="text-xs font-medium text-slate-200 truncate">{{ $product->name }}</h4>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-[10px] text-slate-400 font-mono">{{ $product->brand }}</span>
                        <span class="text-xs font-bold text-emerald-400">${{ number_format($product->price, 2) }}</span>
                    </div>
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

    <!-- Action Buttons -->
    <div class="pt-2 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-end gap-2">
        <a href="{{ route('build.summary', ['product_ids' => collect($products)->pluck('id')->implode(',')]) }}" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-200 text-xs font-semibold px-3.5 py-2.5 rounded-lg transition text-center flex items-center justify-center space-x-1.5">
            <span>📊</span>
            <span>View Build Summary</span>
        </a>
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
