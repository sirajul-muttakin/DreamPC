@extends('layouts.app')

@section('content')
<div class="w-full max-w-3xl mx-auto space-y-6 animate-fade-in">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white font-display">Edit Component: {{ $product->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Update hardware specifications, price, or stock</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-white transition">
            ← Back to Inventory
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 p-4 rounded-2xl text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.update', $product->id) }}" class="bg-white/90 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 shadow-xl backdrop-blur-xl space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Product Name *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">SKU *</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                <select name="category_id" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition font-medium">
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Brand *</label>
                <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition font-medium">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Price ($) *</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Stock Quantity *</label>
                <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition font-mono">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
            <textarea name="description" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition">{{ old('description', $product->description) }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Image URL / Path</label>
            <input type="text" name="image_path" value="{{ old('image_path', $product->image_path) }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-4 py-2.5 text-xs text-slate-900 dark:text-white outline-none transition font-mono">
        </div>

        <!-- Dynamic Specifications Section -->
        <div class="border-t border-slate-100 dark:border-slate-800 pt-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider font-mono">Dynamic Specifications</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Add technical key-value pairs (e.g., socket: AM5, wattage: 120W)</p>
                </div>
                <button type="button" id="add-spec-btn"
                    class="text-xs bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-xl font-bold transition shadow-sm">
                    + Add Spec Row
                </button>
            </div>

            <div id="specs-container" class="space-y-3">
                @php $specs = old('specs', $product->specifications->toArray()); @endphp
                @forelse ($specs as $index => $spec)
                    <div class="flex items-center space-x-3 spec-row">
                        <input type="text" name="specs[{{ $index }}][key]" value="{{ $spec['spec_key'] ?? $spec['key'] ?? '' }}" placeholder="Key (e.g. socket)"
                            class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white outline-none font-mono">
                        <input type="text" name="specs[{{ $index }}][value]" value="{{ $spec['spec_value'] ?? $spec['value'] ?? '' }}" placeholder="Value (e.g. AM5)"
                            class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white outline-none font-mono">
                        <button type="button" class="remove-spec-btn text-red-500 hover:text-red-700 text-xs px-2 py-1 font-bold">✕</button>
                    </div>
                @empty
                    <div class="flex items-center space-x-3 spec-row">
                        <input type="text" name="specs[0][key]" placeholder="Key (e.g. socket)"
                            class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white outline-none font-mono">
                        <input type="text" name="specs[0][value]" placeholder="Value (e.g. AM5)"
                            class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white outline-none font-mono">
                        <button type="button" class="remove-spec-btn text-red-500 hover:text-red-700 text-xs px-2 py-1 font-bold">✕</button>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="pt-4 flex justify-end space-x-3">
            <a href="{{ route('products.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold px-5 py-3 rounded-2xl text-xs transition shadow-sm">
                Cancel
            </a>
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold px-6 py-3 rounded-2xl text-xs transition shadow-lg shadow-blue-500/20 hover:scale-105 transform">
                Update Component
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let specIndex = {{ count($specs) > 0 ? count($specs) : 1 }};
        const container = document.getElementById('specs-container');
        const addBtn = document.getElementById('add-spec-btn');

        addBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'flex items-center space-x-3 spec-row';
            row.innerHTML = `
                <input type="text" name="specs[\${specIndex}][key]" placeholder="Key (e.g. socket)"
                    class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white outline-none font-mono">
                <input type="text" name="specs[\${specIndex}][value]" placeholder="Value (e.g. AM5)"
                    class="w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white outline-none font-mono">
                <button type="button" class="remove-spec-btn text-red-500 hover:text-red-700 text-xs px-2 py-1 font-bold">✕</button>
            `;
            container.appendChild(row);
            specIndex++;
        });

        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-spec-btn')) {
                const row = e.target.closest('.spec-row');
                if (container.querySelectorAll('.spec-row').length > 1) {
                    row.remove();
                } else {
                    row.querySelectorAll('input').forEach(i => i.value = '');
                }
            }
        });
    });
</script>
@endsection
