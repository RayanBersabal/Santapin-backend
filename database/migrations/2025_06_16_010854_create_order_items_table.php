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
        Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // Terhubung ke pesanan mana
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Produk yang dipesan

        // Menyimpan detail produk saat itu juga
        $table->string('product_name'); // Simpan nama produk saat itu
        $table->decimal('price', 10, 2); // Simpan harga produk saat itu (jika harga produk berubah di masa depan, data pesanan lama tidak ikut berubah)
        $table->integer('quantity');

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
