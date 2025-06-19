<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController; // <--- Pastikan ini ada!

// ✅ Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin-login', [AuthController::class, 'adminLogin']);

// ✅ Public Product Access
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ✅ Authenticated User Info & User-specific Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{cart}', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);
    Route::post('/cart/clear', [CartController::class, 'clearCart']);

    // User Order Routes (menggunakan OrderController)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    // HAPUS BARIS INI JIKA ADA di blok ini: Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    // Karena update status akan ditangani oleh AdminOrderController
});

// ✅ Admin-only Routes
// Ini adalah blok untuk rute yang hanya bisa diakses oleh admin
Route::middleware(['auth:sanctum','is_admin'])->prefix('admin')->group(function () { // <--- Pastikan Anda punya prefix('admin') di sini
    // Admin-only Product Management (yang sudah ada)
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // ✅ Admin-only Order Management (TAMBAHKAN BLOK INI)
    // Frontend Vue.js akan memanggil /api/admin/orders
    Route::get('/orders', [AdminOrderController::class, 'index']);
    // Frontend Vue.js akan memanggil /api/admin/orders/{id}/status
    // `{order}` di sini adalah Route Model Binding, Laravel akan otomatis mencari Order berdasarkan ID
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

    // Opsional: Jika admin perlu melihat detail satu order spesifik via /api/admin/orders/{id}
    // Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
});

// ✅ Health check (optional)
Route::get('/', fn () => response()->json(['message' => 'Api Santapin']));
Route::get('/ping', fn () => response()->json(['message' => 'pong']));
