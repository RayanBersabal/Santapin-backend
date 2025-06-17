<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Menampilkan semua item di keranjang milik user yang sedang login.
     * Ini akan dipanggil oleh fetchCart() di Vue.
     */
    public function index()
    {
        // Ambil semua item cart milik user, beserta data produknya (relasi)
        $cartItems = Cart::with('product')
                         ->where('user_id', Auth::id())
                         ->get();

        return response()->json($cartItems);
    }

    /**
     * Menambahkan item baru ke keranjang atau mengupdate quantity jika sudah ada.
     * Ini akan dipanggil oleh addToCart() di Vue.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $productId = $request->product_id;
        $quantity = $request->quantity;

        // Cek apakah produk yang sama sudah ada di keranjang user
        $cartItem = Cart::where('user_id', $user->id)
                        ->where('product_id', $productId)
                        ->first();

        if ($cartItem) {
            // Jika sudah ada, tambahkan quantity-nya
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            // Jika belum ada, buat entri baru
            $cartItem = Cart::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        // Muat relasi produk untuk dikirim kembali sebagai respons
        $cartItem->load('product');

        return response()->json([
            'message' => 'Product added to cart successfully!',
            'cartItem' => $cartItem
        ], 201);
    }

    /**
     * Menghapus satu item dari keranjang.
     * Ini akan dipanggil oleh removeFromCart() di Vue.
     */
    public function destroy(Cart $cart)
    {
        // Pastikan user hanya bisa menghapus item dari keranjangnya sendiri (Authorization)
        if ($cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cart->delete();

        return response()->json(['message' => 'Item removed from cart successfully']);
    }
}
