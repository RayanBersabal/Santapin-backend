<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController; // Pastikan ini di-import
use App\Http\Controllers\OrderController; // Pastikan ini di-import

// ✅ Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin-login', [AuthController::class, 'adminLogin']);

// ✅ Public Product Access
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ✅ Authenticated User Info
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);           // Untuk melihat isi keranjang
    Route::post('/cart', [CartController::class, 'store']);          // Untuk menambah item ke keranjang
    Route::patch('/cart/{cart}', [CartController::class, 'updateQuantity']); // <-- Rute BARU untuk update quantity
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']); // Untuk menghapus item dari keranjang
    Route::post('/cart/clear', [CartController::class, 'clearCart']); // <-- Rute BARU untuk mengosongkan keranjang

    // Order Routes
    Route::post('/orders', [OrderController::class, 'store']);
    // Tambahkan rute untuk melihat pesanan jika diperlukan (misal: Route::get('/orders', [OrderController::class, 'index']);)
    // Akan kita tambahkan nanti jika belum ada
});

// ✅ Admin-only Product Management
Route::middleware(['auth:sanctum','is_admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

// ✅ Health check (optional)
Route::get('/', fn () => response()->json(['message' => 'Api Santapin']));
Route::get('/ping', fn () => response()->json(['message' => 'pong']));
