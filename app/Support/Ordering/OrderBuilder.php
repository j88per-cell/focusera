<?php

namespace App\Support\Ordering;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class OrderBuilder
{
    public static function fromCart(Cart $cart, array $customer, array $shipping): Order
    {
        $order = new Order();
        $order->user_id = $cart->user_id;
        $order->status = 'pending';
        $order->currency = $cart->currency ?? 'USD';
        $order->customer_name = $customer['name'] ?? null;
        $order->customer_email = $customer['email'] ?? null;
        $order->shipping_address = $shipping ?: null;
        $order->subtotal = 0;
        $order->tax_total = 0;
        $order->shipping_total = 0;
        $order->discount_total = 0;
        $order->grand_total = 0;
        $order->save();

        $subtotal = 0.0;
        foreach ($cart->items as $ci) {
            $line = new OrderItem([
                'photo_id' => $ci->photo_id,
                'product_code' => $ci->product_code,
                'variant' => $ci->variant,
                'quantity' => $ci->quantity,
                'unit_price' => $ci->unit_price_final,
                'total_price' => $ci->unit_price_final * $ci->quantity,
                'markup_percent' => $ci->markup_percent,
                'data' => $ci->data,
            ]);
            $order->items()->save($line);
            $subtotal += $line->total_price;
        }

        $order->subtotal = round($subtotal, 2);
        $order->grand_total = round($subtotal + $order->tax_total + $order->shipping_total - $order->discount_total, 2);
        $order->save();

        return $order->fresh('items');
    }
}

