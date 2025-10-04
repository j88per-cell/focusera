<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_featured_public_galleries_and_recent_news(): void
    {
        config([
            'site.analytics.enabled' => false,
            'inertia.testing.ensure_pages_exist' => false,
        ]);

        $featured = Gallery::factory()->count(2)->featured()->create();
        Gallery::factory()->featured()->private()->create();
        Gallery::factory()->create();

        $recent = NewsPost::factory()->count(3)->create([
            'published_at' => now()->subDays(5),
        ]);
        NewsPost::factory()->create(['published_at' => now()->subDay()]);
        NewsPost::factory()->unpublished()->create();

        $response = $this->get('/');

        $response->assertOk()->assertInertia(function (AssertableInertia $page) use ($featured) {
            $page->component('Landing/Index')
                ->where('featuredGalleries', function ($galleries) use ($featured) {
                    $galleries = $galleries instanceof \Illuminate\Support\Collection ? $galleries->all() : $galleries;
                    $this->assertIsArray($galleries);
                    $this->assertCount(2, $galleries);
                    $ids = collect($galleries)->pluck('id')->sort()->values();
                    $this->assertEquals($featured->pluck('id')->sort()->values()->all(), $ids->all());
                    $this->assertTrue(collect($galleries)->every(fn ($gallery) => isset($gallery['photoCount'])));
                    return true;
                })
                ->where('newsPosts', function ($posts) {
                    $posts = $posts instanceof \Illuminate\Support\Collection ? $posts->all() : $posts;
                    $this->assertIsArray($posts);
                    $this->assertLessThanOrEqual(3, count($posts));
                    $this->assertTrue(collect($posts)->every(fn ($post) => !empty($post['publishDate'])));
                    $slugs = collect($posts)->pluck('slug');
                    $this->assertSame($slugs->unique()->count(), $slugs->count());
                    return true;
                });
        });
    }
}
