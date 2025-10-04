<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Photo;
use App\Support\Ordering\OrderBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_order_with_items_and_totals(): void
    {
        $cart = Cart::factory()->forUser()->create();
        $photo = Photo::factory()->create();

        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'photo_id' => $photo->id,
            'unit_price_final' => 25.50,
            'quantity' => 2,
        ]);

        $cart->load('items');

        $order = OrderBuilder::fromCart($cart, ['name' => 'Jane Client', 'email' => 'jane@example.com'], ['country' => 'US']);

        $this->assertNotNull($order->id);
        $this->assertNotNull($order->fresh('items'));
        $this->assertSame($cart->user_id, $order->user_id);
        $this->assertSame('pending', $order->status);
        $this->assertSame(51.0, $order->subtotal);
        $this->assertSame(51.0, $order->grand_total);
        $this->assertCount(1, $order->items);

        $line = $order->items->first();
        $this->assertSame($photo->id, $line->photo_id);
        $this->assertSame(2, $line->quantity);
        $this->assertSame(25.50, $line->unit_price);
        $this->assertSame(51.0, $line->total_price);
    }
}
