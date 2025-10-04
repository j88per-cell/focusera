<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Gallery;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'features.sales' => true,
            'site.analytics.enabled' => false,
            'settings.sales.markup_percent' => 20,
        ]);

        $this->startSession();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_guest_can_add_item_and_markup_applied(): void
    {
        $gallery = Gallery::factory()->create(['markup_percent' => 30]);
        $photo = Photo::factory()->for($gallery)->create(['markup_percent' => null]);

        $response = $this->postJson(route('cart.items.add'), [
            'photo_id' => $photo->id,
            'unit_price_base' => 10,
            'quantity' => 2,
        ]);

        $response->assertCreated();

        $payload = $response->json();
        $this->assertEqualsWithDelta(13.0, $payload['unit_price_final'], 0.001);
        $this->assertEqualsWithDelta(30.0, $payload['markup_percent'], 0.001);
        $this->assertSame(2, $payload['quantity']);

        $cart = Cart::where('session_id', session()->getId())->first();
        $this->assertNotNull($cart);
        $this->assertSame(session()->getId(), $cart->session_id);
        $this->assertCount(1, $cart->items);
    }

    public function test_guest_can_update_and_remove_items_from_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'session_id' => null,
        ]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'photo_id' => Photo::factory()->create()->id,
            'quantity' => 1,
        ]);

        $update = $this->patchJson(route('cart.items.update', $item), ['quantity' => 4]);
        $update->assertOk()->assertJson(['quantity' => 4]);

        $remove = $this->delete(route('cart.items.remove', $item));
        $remove->assertNoContent();

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_guest_cannot_modify_items_from_other_cart(): void
    {
        Auth::logout();
        $cart = Cart::factory()->create(['session_id' => 'other-session']);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 1,
        ]);

        $response = $this->patchJson(route('cart.items.update', $item), ['quantity' => 2]);
        $response->assertNotFound();
    }

    public function test_clear_endpoint_removes_all_items(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'session_id' => null,
        ]);
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $response = $this->delete(route('cart.clear'));
        $response->assertNoContent();

        $this->assertSame(0, $cart->fresh()->items()->count());
    }
}
