<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)->with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('best_seller')) {
            $query->where('is_best_seller', true);
        }

        if ($request->has('new')) {
            $query->where('is_new', true);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductResource($product->load('category'));
    }

    public function featured()
    {
        $products = Product::where('is_active', true)
            ->where('is_best_seller', true)
            ->limit(6)
            ->get();

        return ProductResource::collection($products);
    }
}
