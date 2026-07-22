@extends('layouts.app')

@section('content')
<div class="w-full space-y-8 animate-fade-in">
    
    <!-- Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-700 p-8 sm:p-10 shadow-2xl text-white">
        <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider font-mono">
                    <span>⚡</span> 100% Tested Hardware Inventory
                </div>
                <h1 class="text-3xl sm:text-4xl font-black font-display tracking-tight">
                    Custom PC Components & Rigs
                </h1>
                <p class="text-xs sm:text-sm text-blue-100 leading-relaxed">
                    Filter by exact socket types, DDR memory standards, PCIe bandwidth, or let our AI Hardware Assistant configure your complete build.
                </p>
            </div>
            
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('chat.index') }}" class="bg-white text-blue-700 hover:bg-blue-50 font-bold px-5 py-3 rounded-2xl text-xs transition shadow-lg shadow-black/10 hover:scale-105 transform flex items-center gap-2">
                    <span class="text-base">🤖</span>
                    <span>AI Build Assistant</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Controls Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/80 dark:bg-slate-900/80 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm backdrop-blur-xl">
        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
            Showing <strong class="text-slate-900 dark:text-white font-mono">{{ $products->total() }}</strong> Available Hardware Parts
        </div>
        
        <!-- Sort Control -->
        <form method="GET" action="{{ route('catalog.index') }}" class="flex items-center space-x-2">
            @foreach(request()->except('sort', 'page') as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Sort By:</label>
            <select name="sort" onchange="this.form.submit()" 
                class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 outline-none focus:border-blue-500 transition font-medium">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
            </select>
        </form>
    </div>

    <!-- Catalog Content: Sidebar + Products Grid -->
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Filter Sidebar -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <form method="GET" action="{{ route('catalog.index') }}" class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xl backdrop-blur-xl space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider font-mono">Filters</h2>
                    @if(request()->anyFilled(['category', 'brand', 'min_price', 'max_price', 'spec_key', 'spec_value']))
                        <a href="{{ route('catalog.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold">Reset All</a>
                    @endif
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wider">Category</label>
                    <select name="category" onchange="this.form.submit()"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2.5 outline-none focus:border-blue-500 transition">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wider">Brand</label>
                    <select name="brand" onchange="this.form.submit()"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2.5 outline-none focus:border-blue-500 transition">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            @if($brand)
                                <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                    {{ $brand }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Price Range Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wider">Price Range ($)</label>
                    <div class="flex items-center space-x-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" min="0" step="1"
                            class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                        <span class="text-slate-400">-</span>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" min="0" step="1"
                            class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                    </div>
                </div>

                <!-- Specification Filter -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Spec Filter</label>
                    <input type="text" name="spec_key" value="{{ request('spec_key') }}" placeholder="Key (e.g. socket, tdp)"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                    <input type="text" name="spec_value" value="{{ request('spec_value') }}" placeholder="Value (e.g. AM5, 65W)"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3 rounded-2xl text-xs transition shadow-lg shadow-blue-500/20 hover:scale-[1.02] transform">
                    Apply Filters
                </button>
            </form>
        </aside>

        <!-- Product Grid Display -->
        <main class="flex-grow">
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl hover:border-blue-500/40 dark:hover:border-blue-500/40 transition-all duration-300 flex flex-col justify-between group backdrop-blur-xl">
                            <div>
                                <!-- Product Image with Zoom on Hover -->
                                <div class="h-48 bg-slate-100 dark:bg-slate-950 flex items-center justify-center border-b border-slate-100 dark:border-slate-800 relative overflow-hidden">
                                    @if($product->image_path)
                                        <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="h-full w-full object-cover transform group-hover:scale-108 transition-all duration-500">
                                    @else
                                        <div class="text-4xl text-slate-400">📦</div>
                                    @endif
                                    
                                    <!-- Stock Status Badge -->
                                    <div class="absolute top-3 right-3">
                                        @if($product->stock_quantity > 5)
                                            <span class="bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[10px] px-2.5 py-1 rounded-full font-bold border border-emerald-500/30 backdrop-blur-md">
                                                In Stock ({{ $product->stock_quantity }})
                                            </span>
                                        @elseif($product->stock_quantity > 0)
                                            <span class="bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] px-2.5 py-1 rounded-full font-bold border border-amber-500/30 backdrop-blur-md">
                                                Low Stock ({{ $product->stock_quantity }})
                                            </span>
                                        @else
                                            <span class="bg-red-500/20 text-red-700 dark:text-red-300 text-[10px] px-2.5 py-1 rounded-full font-bold border border-red-500/30 backdrop-blur-md">
                                                Out of Stock
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Card Details -->
                                <div class="p-5 space-y-3">
                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                        <span class="font-mono font-medium">{{ $product->brand }}</span>
                                        <span class="bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-lg text-[10px] font-mono text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-bold uppercase">
                                            {{ $product->category->name ?? 'Hardware' }}
                                        </span>
                                    </div>

                                    <h3 class="font-bold text-slate-900 dark:text-white text-base group-hover:text-blue-600 dark:group-hover:text-blue-400 transition line-clamp-1 font-display">
                                        {{ $product->name }}
                                    </h3>

                                    <!-- Specifications Snapshot -->
                                    @if($product->specifications->count() > 0)
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            @foreach($product->specifications->take(3) as $spec)
                                                <span class="bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 text-[10px] px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-slate-800 font-mono">
                                                    {{ $spec->spec_key }}: <strong class="text-slate-800 dark:text-slate-200">{{ $spec->spec_value }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer Price & Async Add to Cart -->
                            <div class="p-5 pt-0 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 mt-4">
                                <div class="pt-3">
                                    <div class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">Price</div>
                                    <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">${{ number_format($product->price, 2) }}</div>
                                </div>

                                <div class="pt-3">
                                    <button type="button" onclick="asyncAddToCart({{ $product->id }}, this)" 
                                            class="bg-slate-900 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-500/20 flex items-center space-x-1.5 hover:scale-105 transform">
                                        <span>🛒</span>
                                        <span>Add to Cart</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bg-white/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-12 text-center text-slate-500 dark:text-slate-400 shadow-xl">
                    <div class="text-5xl mb-3">🔍</div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">No products found</h3>
                    <p class="text-xs text-slate-500 mt-1">Try adjusting or resetting your filter criteria.</p>
                </div>
            @endif
        </main>

    </div>
</div>

<script>
async function asyncAddToCart(productId, btnElement) {
    const originalText = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = '<span>⏳</span> <span>Adding...</span>';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ product_ids: [productId] })
        });

        if (response.ok) {
            btnElement.innerHTML = '<span>✓</span> <span>Added!</span>';
            btnElement.classList.remove('bg-slate-900', 'dark:bg-blue-600');
            btnElement.classList.add('bg-emerald-600');

            // Update navbar badge if available
            const badge = document.getElementById('nav-cart-badge');
            if (badge) {
                const current = parseInt(badge.textContent.trim()) || 0;
                badge.textContent = current + 1;
            }

            window.showToast('Item added to cart successfully!', 'success');

            setTimeout(() => {
                btnElement.innerHTML = originalText;
                btnElement.classList.remove('bg-emerald-600');
                btnElement.classList.add('bg-slate-900', 'dark:bg-blue-600');
                btnElement.disabled = false;
            }, 1800);
        } else {
            throw new Error('Failed to add to cart');
        }
    } catch (e) {
        console.error(e);
        btnElement.innerHTML = originalText;
        btnElement.disabled = false;
        window.showToast('Could not add item to cart. Please try again.', 'error');
    }
}
</script>
@endsection
