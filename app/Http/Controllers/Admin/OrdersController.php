<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->paginate(20, ['id','status','grand_total','currency','external_id','created_at']);
        return Inertia::render('Orders/Index', [ 'orders' => $orders ])->rootView('admin');
    }

    public function show(Order $order)
    {
        $order->load('items.photo','invoices');
        return Inertia::render('Orders/Show', [ 'order' => $order ])->rootView('admin');
    }
}

