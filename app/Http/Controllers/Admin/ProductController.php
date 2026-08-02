<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Specification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'specifications'])
            ->latest()
            ->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string'],
            'specs' => ['nullable', 'array'],
            'specs.*.key' => ['nullable', 'string', 'max:100'],
            'specs.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'category_id' => $validated['category_id'],
            'brand' => $validated['brand'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'description' => $validated['description'] ?? null,
            'image_path' => $validated['image_path'] ?? null,
        ]);

        if (!empty($validated['specs'])) {
            foreach ($validated['specs'] as $spec) {
                if (!empty($spec['key']) && !empty($spec['value'])) {
                    Specification::create([
                        'product_id' => $product->id,
                        'spec_key' => $spec['key'],
                        'spec_value' => $spec['value'],
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'specifications']);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'category_id' => ['required', 'exists:categories,id'],
            'brand' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string'],
            'specs' => ['nullable', 'array'],
            'specs.*.key' => ['nullable', 'string', 'max:100'],
            'specs.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'category_id' => $validated['category_id'],
            'brand' => $validated['brand'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'description' => $validated['description'] ?? null,
            'image_path' => $validated['image_path'] ?? null,
        ]);

        $product->specifications()->delete();

        if (!empty($validated['specs'])) {
            foreach ($validated['specs'] as $spec) {
                if (!empty($spec['key']) && !empty($spec['value'])) {
                    Specification::create([
                        'product_id' => $product->id,
                        'spec_key' => $spec['key'],
                        'spec_value' => $spec['value'],
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
