<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlistItems = $request->user()
            ->wishlists()
            ->with('product')
            ->get()
            ->pluck('product');

        return ProductResource::collection($wishlistItems);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $product = Product::findOrFail($request->product_id);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => $wishlist->wasRecentlyCreated ? 'Product added to wishlist' : 'Product already in wishlist',
            'product' => new ProductResource($product)
        ]);
    }

    public function destroy(Request $request, $productId)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->first();

        if (!$wishlist) {
            return response()->json(['message' => 'Product not in wishlist'], 404);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Product removed from wishlist']);
    }
}
