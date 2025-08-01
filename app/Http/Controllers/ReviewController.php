<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index(Product $product)
    {
        return response()->json($product->reviews()->with('user')->latest()->get());
    }


    public function store(Request $request)
    {
        try {
            // Validasi data yang dikirim dari frontend
            $validatedData = $request->validate([
                'orderId' => 'required|exists:orders,id',
                'productId' => 'required|exists:products,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            // Periksa apakah user sudah mereview produk ini melalui order ini
            // Ini akan mencegah user mereview produk yang sama di pesanan yang sama
            $existingReview = Review::where('user_id', $request->user()->id)
                                    ->where('product_id', $validatedData['productId'])
                                    ->where('order_id', $validatedData['orderId'])
                                    ->first();

            if ($existingReview) {
                return response()->json([
                    'message' => 'Anda sudah memberikan review untuk produk ini pada pesanan yang sama.'
                ], 409);
            }

            $review = Review::create([
                'user_id' => $request->user()->id,
                'order_id' => $validatedData['orderId'],
                'product_id' => $validatedData['productId'],
                'rating' => $validatedData['rating'],
                'comment' => $validatedData['comment'] ?? null,
            ]);

            $review->load('user');

            return response()->json([
                'message' => 'Review berhasil dikirim!',
                'review' => $review
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengirim review.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Metode update dan destroy tidak diubah
    public function update(Request $request, Review $review)
    {
        // Authorization: Hanya pemilik review atau admin yang bisa update
        if ($request->user()->id !== $review->user_id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $validatedData = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $review->update($validatedData);
            $review->load('user');

            return response()->json([
                'message' => 'Review berhasil diperbarui!',
                'review' => $review
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui review.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, Review $review)
    {
        // Authorization: Hanya pemilik review atau admin yang bisa menghapus
        if ($request->user()->id !== $review->user_id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $review->delete();
            return response()->json(['message' => 'Review berhasil dihapus!'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus review.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
