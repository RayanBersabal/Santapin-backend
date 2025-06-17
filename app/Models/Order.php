<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'shipping_cost',
        'admin_fee',
        'total_amount',
        'status',
        'customer_details',
        'payment_method',
        'payment_status',
    ];
    // Casts ini akan mengubah kolom JSON menjadi array/object secara otomatis
    protected $casts = [
        'customer_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
