<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User; // Pastikan ini di-import
use App\Models\OrderItem; // Pastikan ini di-import jika Anda akan membuat order item secara manual
use App\Models\Product; // Pastikan ini di-import jika Anda memerlukannya
use Illuminate\Support\Facades\Auth; // Digunakan jika Anda menggunakan Auth::user()

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     * Mengambil daftar pesanan untuk pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        // Mendapatkan pengguna yang sedang login melalui Sanctum
        // Pastikan token otentikasi dikirim dari frontend
        $user = $request->user();

        // Ambil semua pesanan untuk user ini, urutkan dari terbaru.
        // Eager load relasi 'orderItems' dan di dalamnya 'product'.
        $orders = $user->orders()->with('orderItems.product')->latest()->get();

        // Sesuaikan format respons agar sesuai dengan frontend Vue.js Anda
        return response()->json([
            'status' => 'success',
            'message' => 'User orders fetched successfully.',
            'data' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'total' => $order->total_amount, // Kolom total_amount dari tabel orders
                    'status' => $order->status,
                    'createdAt' => $order->created_at, // Timestamp created_at
                    'form' => [
                        'namaLengkap' => $order->customer_name,
                        'nomorHp' => $order->customer_phone,
                        'alamatPengantaran' => $order->delivery_address,
                        'pesanUntukDapur' => $order->notes,
                    ],
                    'ongkir' => $order->delivery_fee,
                    'biayaAdmin' => $order->admin_fee,
                    // Memetakan item-item pesanan
                    'items' => $order->orderItems->map(function($item) {
                        return [
                            // Prioritaskan product_name dari OrderItem, fallback ke product->name
                            'name' => $item->product_name ?? ($item->product ? $item->product->name : 'Unknown Product'),
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            // Anda bisa menambahkan 'image_url' di sini jika model Product memiliki itu
                            // 'image_url' => $item->product ? $item->product->image_url : null,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Display the specified order.
     * Mengambil detail spesifik dari satu pesanan berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        // Ambil pesanan berdasarkan ID, pastikan itu milik pengguna yang login.
        // Eager load relasi 'orderItems' dan di dalamnya 'product'.
        $order = $user->orders()->with('orderItems.product')->findOrFail($id);

        // Sesuaikan format respons agar sesuai dengan frontend Vue.js Anda
        return response()->json([
            'status' => 'success',
            'message' => 'Order details fetched successfully.',
            'data' => [
                'id' => $order->id,
                'total' => $order->total_amount,
                'status' => $order->status,
                'createdAt' => $order->created_at,
                'form' => [
                    'namaLengkap' => $order->customer_name,
                    'nomorHp' => $order->customer_phone,
                    'alamatPengantaran' => $order->delivery_address,
                    'pesanUntukDapur' => $order->notes,
                ],
                'ongkir' => $order->delivery_fee,
                'biayaAdmin' => $order->admin_fee,
                'items' => $order->orderItems->map(function($item) {
                    return [
                        'name' => $item->product_name ?? ($item->product ? $item->product->name : 'Unknown Product'),
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        // Anda bisa menambahkan 'image_url' di sini jika model Product memiliki itu
                        // 'image_url' => $item->product ? $item->product->image_url : null,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Store a newly created order in storage.
     * Membuat pesanan baru dari data yang dikirimkan (biasanya dari keranjang).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validasi data yang masuk
        $validatedData = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
            'payment_type' => 'required|string|in:Cash on Delivery,Online Payment', // Sesuaikan
            // delivery_fee, admin_fee, subtotal, total_amount akan dihitung di backend
        ]);

        $subtotal = 0;
        $deliveryFee = 10000; // Contoh: biaya pengiriman tetap
        $adminFee = 2000;    // Contoh: biaya admin tetap
        $orderItemsData = [];

        // Hitung subtotal dan siapkan data item pesanan
        foreach ($validatedData['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemPrice = $product->price; // Ambil harga terbaru dari produk
            $itemSubtotal = $itemPrice * $item['quantity'];
            $subtotal += $itemSubtotal;

            $orderItemsData[] = new OrderItem([
                'product_id' => $product->id,
                'product_name' => $product->name, // Simpan nama produk saat ini
                'price' => $itemPrice,           // Simpan harga produk saat ini
                'quantity' => $item['quantity'],
            ]);
        }

        $totalAmount = $subtotal + $deliveryFee + $adminFee;

        // Buat pesanan baru
        $order = $user->orders()->create([
            'customer_name' => $validatedData['customer_name'],
            'customer_phone' => $validatedData['customer_phone'],
            'delivery_address' => $validatedData['delivery_address'],
            'notes' => $validatedData['notes'],
            'payment_type' => $validatedData['payment_type'],
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'admin_fee' => $adminFee,
            'total_amount' => $totalAmount,
            'payment_status' => 'pending', // Default saat order dibuat
            'status' => 'Dipesan', // Status awal pesanan
            // 'estimated_delivery_time' => null, // Opsional, bisa diatur nanti
        ]);

        // Simpan item-item pesanan yang terkait
        $order->orderItems()->saveMany($orderItemsData);

        // Eager load orderItems dan product untuk respons
        $order->load('orderItems.product');

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil dibuat!',
            'data' => [
                'id' => $order->id,
                'total' => $order->total_amount,
                'status' => $order->status,
                'createdAt' => $order->created_at,
                'form' => [
                    'namaLengkap' => $order->customer_name,
                    'nomorHp' => $order->customer_phone,
                    'alamatPengantaran' => $order->delivery_address,
                    'pesanUntukDapur' => $order->notes,
                ],
                'ongkir' => $order->delivery_fee,
                'biayaAdmin' => $order->admin_fee,
                'items' => $order->orderItems->map(function($item) {
                    return [
                        'name' => $item->product_name, // Di sini yakin product_name ada
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
            ]
        ], 201); // 201 Created
    }

    /**
     * Update the specified order status in storage.
     * Mengupdate status pesanan (contoh untuk admin/proses internal).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Dipesan,Disiapkan,Dikirim,Selesai,Dibatalkan', // Sesuaikan status yang valid
        ]);

        $order = Order::findOrFail($id);

        // Pastikan hanya admin atau pemilik pesanan yang bisa update (tergantung logika bisnis)
        // if ($request->user()->id !== $order->user_id && !$request->user()->isAdmin()) {
        //     abort(403, 'Unauthorized action.');
        // }

        $order->status = $request->status;
        $order->save();

        // Eager load relasi untuk respons
        $order->load('orderItems.product');

        return response()->json([
            'status' => 'success',
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => [
                'id' => $order->id,
                'total' => $order->total_amount,
                'status' => $order->status,
                'createdAt' => $order->created_at,
                'form' => [
                    'namaLengkap' => $order->customer_name,
                    'nomorHp' => $order->customer_phone,
                    'alamatPengantaran' => $order->delivery_address,
                    'pesanUntukDapur' => $order->notes,
                ],
                'ongkir' => $order->delivery_fee,
                'biayaAdmin' => $order->admin_fee,
                'items' => $order->orderItems->map(function($item) {
                    return [
                        'name' => $item->product_name ?? ($item->product ? $item->product->name : 'Unknown Product'),
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
            ]
        ]);
    }

    // ... Anda bisa menambahkan metode lain seperti cancelOrder, dll.
}
