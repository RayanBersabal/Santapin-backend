<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    // Tambahkan 'order_id' ke dalam array $fillable
    protected $fillable = ['user_id', 'product_id', 'order_id', 'rating', 'comment'];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Tambahkan relasi ke Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
