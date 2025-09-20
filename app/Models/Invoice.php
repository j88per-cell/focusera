<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','number','status','currency','amount_total','issued_at','paid_at','data',
    ];

    protected $casts = [
        'amount_total' => 'float',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'data' => 'array',
    ];

    public function order() { return $this->belongsTo(Order::class); }
}

