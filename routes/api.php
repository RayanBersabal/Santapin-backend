<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin-login', [AuthController::class, 'adminLogin']);

// Public Product Access (GET only)
// Ini adalah rute untuk semua user, termasuk yang tidak login, untuk melihat produk.
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Authenticated User Info & User-specific Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{cart}', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);
    Route::post('/cart/clear', [CartController::class, 'clearCart']);

    // User Order Routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
});

// Admin-only Routes
// Ini adalah blok untuk rute yang hanya bisa diakses oleh admin
Route::middleware(['auth:sanctum','is_admin'])->prefix('admin')->group(function () {
    // Admin-only Product Management (POST, PUT, DELETE for products)
    // Frontend akan memanggil /api/admin/products untuk POST (create)
    Route::post('/products', [ProductController::class, 'store']);
    // Frontend akan memanggil /api/admin/products/{id} untuk PUT (update)
    Route::put('/products/{id}', [ProductController::class, 'update']);
    // Frontend akan memanggil /api/admin/products/{id} untuk DELETE
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Admin-only Order Management
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
    // Route::get('/orders/{order}', [AdminOrderController::class, 'show']); // Opsional
});

// Health check (optional)
Route::get('/', fn () => response()->json(['message' => 'Api Santapin']));
Route::get('/ping', fn () => response()->json(['message' => 'pong']));
