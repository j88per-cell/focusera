<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        $base = $this->faker->randomFloat(2, 5, 100);
        $percent = $this->faker->randomFloat(1, 0, 100);
        $final = round($base * (1 + $percent / 100), 2);

        return [
            'cart_id' => Cart::factory(),
            'photo_id' => Photo::factory(),
            'product_code' => $this->faker->optional()->lexify('PROD-????'),
            'variant' => $this->faker->optional()->word(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price_base' => $base,
            'unit_price_final' => $final,
            'markup_percent' => $percent,
            'data' => null,
        ];
    }

    public function withCart(Cart $cart): static
    {
        return $this->state(fn () => ['cart_id' => $cart->id]);
    }

    public function withPhoto(Photo $photo): static
    {
        return $this->state(fn () => ['photo_id' => $photo->id]);
    }
}
