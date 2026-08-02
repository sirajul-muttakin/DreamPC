@extends('layouts.app')

@section('content')
    <div class="w-full max-w-6xl mx-auto space-y-6 animate-fade-in">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white font-display">Hardware Inventory</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage PC components, pricing, stock, and specifications</p>
            </div>
            <a href="{{ route('products.create') }}"
                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold px-5 py-2.5 rounded-2xl text-xs transition shadow-lg shadow-blue-500/20 text-center hover:scale-105 transform">
                + Add New Component
            </a>
        </div>

        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 p-4 rounded-2xl text-xs">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white/90 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xl backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">
                            <th class="py-4 px-6">Product</th>
                            <th class="py-4 px-6">SKU</th>
                            <th class="py-4 px-6">Category</th>
                            <th class="py-4 px-6">Price</th>
                            <th class="py-4 px-6">Stock</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                        @forelse ($products as $product)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 dark:text-white font-display text-sm">{{ $product->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $product->brand }}</div>
                                </td>
                                <td class="py-4 px-6 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $product->sku }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-block bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold px-2.5 py-0.5 rounded-lg border border-slate-200 dark:border-slate-700/60 font-mono uppercase">
                                        {{ $product->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-black text-emerald-600 dark:text-emerald-400 font-mono">${{ number_format($product->price, 2) }}</td>
                                <td class="py-4 px-6">
                                    @if ($product->stock_quantity > 5)
                                        <span class="bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[10px] px-2.5 py-1 rounded-full font-bold border border-emerald-500/20 font-mono">
                                            {{ $product->stock_quantity }} In Stock
                                        </span>
                                    @elseif ($product->stock_quantity > 0)
                                        <span class="bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[10px] px-2.5 py-1 rounded-full font-bold border border-amber-500/20 font-mono">
                                            Low Stock ({{ $product->stock_quantity }})
                                        </span>
                                    @else
                                        <span class="bg-red-500/10 text-red-700 dark:text-red-400 text-[10px] px-2.5 py-1 rounded-full font-bold border border-red-500/20 font-mono">
                                            Out of Stock
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-3">
                                    <a href="{{ route('products.edit', $product->id) }}"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-bold">Edit</a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this component?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-xs text-red-600 dark:text-red-400 hover:underline font-bold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    No hardware components found in inventory.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection