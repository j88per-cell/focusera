<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\NewsPost;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    public function index() {
        $featuredGalleries = Gallery::query()
            ->where('public', true)
            ->where('featured', true)
            ->withCount('photos')
            ->inRandomOrder()
            ->limit(12)
            ->get()
            ->map(function (Gallery $gallery) {
                return [
                    'id' => $gallery->id,
                    'title' => $gallery->title,
                    'description' => $gallery->description,
                    'thumbnail' => $gallery->thumbnail_url,
                    'photoCount' => $gallery->photos_count ?? 0,
                    'date' => $gallery->date ? $gallery->date->format('M Y') : null,
                ];
            })
            ->values();

        $newsPosts = NewsPost::query()
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(function (NewsPost $post) {
                $author = $post->author;
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'publishDate' => optional($post->published_at)->format('M j, Y'),
                    'slug' => $post->slug,
                    'url' => route('news.show', $post->slug),
                    'featuredImage' => $post->featured_image ?? null,
                    'category' => $post->category ?? 'News',
                    'author' => [
                        'name' => $author?->name ?? 'Studio Team',
                        'avatar' => $author?->avatar_url ?? null,
                    ],
                ];
            })
            ->values();

        return Inertia::render('Landing/Index', [
            'featuredGalleries' => $featuredGalleries,
            'newsPosts' => $newsPosts,
        ]);
    }
}
