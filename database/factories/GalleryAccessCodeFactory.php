<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\GalleryAccessCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<GalleryAccessCode>
 */
class GalleryAccessCodeFactory extends Factory
{
    protected $model = GalleryAccessCode::class;

    public function definition(): array
    {
        $code = strtoupper(Str::random(8));

        return [
            'gallery_id' => Gallery::factory(),
            'code_hash' => Hash::make($code),
            'label' => $this->faker->optional()->words(2, true),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }

    public function withPlainCode(string $plain): static
    {
        return $this->state(fn () => ['code_hash' => Hash::make($plain)]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }
}
