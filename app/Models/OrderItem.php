<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','photo_id','product_code','variant','quantity','unit_price','total_price','markup_percent','data',
    ];

    protected $casts = [
        'quantity' => 'int',
        'unit_price' => 'float',
        'total_price' => 'float',
        'markup_percent' => 'float',
        'data' => 'array',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function photo() { return $this->belongsTo(Photo::class); }
}

