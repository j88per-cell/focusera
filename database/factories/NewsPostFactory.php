<?php

namespace Database\Factories;

use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsPost>
 */
class NewsPostFactory extends Factory
{
    protected $model = NewsPost::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(4),
            'excerpt' => $this->faker->sentence(8),
            'body' => $this->faker->paragraphs(3, true),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'author_id' => User::factory(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }

    public function withoutSlug(): static
    {
        return $this->state(fn () => ['slug' => null]);
    }
}
