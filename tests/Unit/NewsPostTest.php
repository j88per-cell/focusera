<?php

namespace Tests\Unit;

use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewsPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_unique_slug_on_save(): void
    {
        $title = 'Autumn Wedding Showcase';

        $first = NewsPost::factory()->withoutSlug()->create(['title' => $title]);
        $second = NewsPost::factory()->withoutSlug()->create(['title' => $title]);

        $this->assertTrue(Str::startsWith($first->slug, Str::slug($title)));
        $this->assertTrue(Str::startsWith($second->slug, Str::slug($title)));
        $this->assertNotSame($first->slug, $second->slug);
    }
}
