<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'specifications']);

        // Filter by Category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('id', $request->category);
            });
        }

        // Filter by Brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter by Price Range
        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->whereBetween('price', [(float)$request->min_price, (float)$request->max_price]);
        } elseif ($request->filled('min_price')) {
            $query->where('price', '>=', (float)$request->min_price);
        } elseif ($request->filled('max_price')) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        // Filter by Specific Key-Value Specifications
        if ($request->filled('spec_key') && $request->filled('spec_value')) {
            $query->whereHas('specifications', function ($q) use ($request) {
                $q->where('spec_key', $request->spec_key)
                  ->where('spec_value', 'LIKE', '%' . $request->spec_value . '%');
            });
        } elseif ($request->filled('spec_key')) {
            $query->whereHas('specifications', function ($q) use ($request) {
                $q->where('spec_key', $request->spec_key);
            });
        }

        // Sorting
        switch ($request->get('sort')) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();
        $brands = Product::select('brand')->distinct()->pluck('brand');

        return view('catalog.index', compact('products', 'categories', 'brands'));
    }
}
