<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Siapa yang memesan

        // Informasi dari form checkout
        $table->string('customer_name');        // Nama Lengkap
        $table->string('customer_phone');       // Nomor HP
        $table->text('delivery_address');       // Alamat Pengantaran
        $table->text('notes')->nullable();      // Pesan untuk Dapur (opsional)

        // Informasi Pembayaran dan Harga
        $table->string('payment_type');         // Tipe Pembayaran (e.g., 'cod', 'wallet', 'bank_transfer')
        $table->decimal('subtotal', 10, 2);     // Subtotal
        $table->decimal('delivery_fee', 10, 2); // Ongkir
        $table->decimal('admin_fee', 10, 2);    // Biaya Admin
        $table->decimal('total_amount', 10, 2); // Total

        // Status dan Informasi Tambahan
        $table->string('status')->default('pending'); // Status pesanan: pending, processing, delivering, completed, cancelled
        $table->string('estimated_delivery_time')->nullable(); // Estimasi Waktu Pengantaran

        $table->timestamps(); // created_at dan updated_at
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
