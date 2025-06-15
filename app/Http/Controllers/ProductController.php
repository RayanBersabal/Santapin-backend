<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Public: Get all products
    public function index()
    {
        return Product::all();
    }

    // Admin: Create product
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'category'    => 'required|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'category'    => $request->category,
            'image'       => $imagePath,
        ]);

        return response()->json($product, 201);
    }

    // Public: Show a single product
    public function show(Product $product)
    {
        return $product;
    }

    // Admin: Update product
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'sometimes|string',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric',
            'category'    => 'sometimes|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update($request->except('image'));

        return response()->json($product);
    }

    // Admin: Delete product
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
