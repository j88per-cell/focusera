<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title' => $title,
            'description' => $this->faker->sentence(),
            'attribution' => $this->faker->optional()->company(),
            'notes' => $this->faker->optional()->paragraph(),
            'date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'public' => true,
            'featured' => false,
            'allow_orders' => false,
            'markup_percent' => null,
            'access_code' => null,
            'thumbnail' => null,
            'parent_id' => null,
            'exif_visibility' => 'all',
            'exif_fields' => null,
        ];
    }

    public function private(): static
    {
        return $this->state(fn () => ['public' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function withAccessCode(string $code = null): static
    {
        return $this->state(function () use ($code) {
            return ['access_code' => $code ?? strtoupper(Str::random(8))];
        });
    }
}
