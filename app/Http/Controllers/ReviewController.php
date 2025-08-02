<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Mengambil daftar ulasan, bisa difilter berdasarkan product_id atau order_id.
     */
    public function index(Request $request)
    {
        // Query builder untuk ulasan
        $query = Review::with('user')->latest();

        // Filter berdasarkan product_id jika ada
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        // Filter berdasarkan order_id jika ada
        if ($request->has('order_id')) {
            $query->where('order_id', $request->get('order_id'));
        }

        return response()->json([
            'data' => $query->get()
        ]);
    }

    /**
     * Menyimpan ulasan baru ke database.
     */
    public function store(Request $request)
    {
        try {
            // Perbaiki: Validasi menggunakan snake_case untuk order_id dan product_id
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|exists:products,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $existingReview = Review::where('user_id', $request->user()->id)
                                        ->where('order_id', $validatedData['order_id'])
                                        ->where('product_id', $validatedData['product_id'])
                                        ->exists();

            if ($existingReview) {
                return response()->json([
                    'message' => 'Anda sudah memberikan review untuk produk ini pada pesanan yang sama.'
                ], 409);
            }

            // Perbaiki: Gunakan data dari $validatedData
            $review = Review::create([
                'user_id' => $request->user()->id,
                'order_id' => $validatedData['order_id'],
                'product_id' => $validatedData['product_id'],
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

    /**
     * Memperbarui ulasan yang sudah ada.
     */
    public function update(Request $request, Review $review)
    {
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

    /**
     * Menghapus ulasan.
     */
    public function destroy(Request $request, Review $review)
    {
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
