<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk dari frontend
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string',
            'notes' => 'nullable|string',
            'payment_type' => 'required|string',
            'subtotal' => 'required|numeric',
            'delivery_fee' => 'required|numeric',
            'admin_fee' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        // Gunakan Database Transaction untuk memastikan semua query berhasil
        try {
            DB::beginTransaction();

            // 2. Buat entri di tabel 'orders'
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_address' => $validated['delivery_address'],
                'notes' => $validated['notes'],
                'payment_type' => $validated['payment_type'],
                'subtotal' => $validated['subtotal'],
                'delivery_fee' => $validated['delivery_fee'],
                'admin_fee' => $validated['admin_fee'],
                'total_amount' => $validated['total_amount'],
                'status' => 'pending', // Status awal
                'estimated_delivery_time' => '45 - 60 menit', // Bisa dibuat dinamis jika perlu
            ]);

            // 3. Loop dan buat entri di tabel 'order_items'
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'], // Ambil dari data yang dikirim Vue
                    'price' => $item['price'],       // Ambil dari data yang dikirim Vue
                    'quantity' => $item['quantity'],
                ]);
            }

            // NOTE: Logika untuk mengosongkan keranjang belanja di database akan ditambahkan di sini nanti
            // setelah CartController dibuat. Untuk sekarang, ini sudah cukup.

            DB::commit();

            // 4. Kirim respons sukses
            return response()->json([
                'message' => 'Order created successfully!',
                'order_id' => $order->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Kirim respons error
            return response()->json([
                'message' => 'Failed to create order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
