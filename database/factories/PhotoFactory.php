<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        $filename = Str::slug($this->faker->words(3, true));

        return [
            'gallery_id' => Gallery::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'attribution' => $this->faker->optional()->name(),
            'notes' => $this->faker->optional()->paragraph(),
            'exif' => null,
            'lat' => null,
            'long' => null,
            'path_original' => "originals/{$filename}.jpg",
            'path_web' => "web/{$filename}.jpg",
            'path_thumb' => "thumbs/{$filename}.jpg",
            'markup_percent' => null,
        ];
    }

    public function withExif(array $exif): static
    {
        return $this->state(fn () => ['exif' => $exif]);
    }
}
