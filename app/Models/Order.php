<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','status','currency','subtotal','tax_total','shipping_total','discount_total','grand_total',
        'external_id','customer_name','customer_email','shipping_address','data',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'tax_total' => 'float',
        'shipping_total' => 'float',
        'discount_total' => 'float',
        'grand_total' => 'float',
        'shipping_address' => 'array',
        'data' => 'array',
    ];

    public function items() { return $this->hasMany(OrderItem::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function user() { return $this->belongsTo(User::class); }
}

