<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id','photo_id','product_code','variant','quantity','unit_price_base','unit_price_final','markup_percent','data',
    ];

    protected $casts = [
        'quantity' => 'int',
        'unit_price_base' => 'float',
        'unit_price_final' => 'float',
        'markup_percent' => 'float',
        'data' => 'array',
    ];

    public function cart() { return $this->belongsTo(Cart::class); }
    public function photo() { return $this->belongsTo(Photo::class); }
}

