<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $posts = NewsPost::query()
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->paginate(10, ['id','title','slug','excerpt','published_at']);
        return inertia('News/Index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = NewsPost::where('slug', $slug)->whereNotNull('published_at')->firstOrFail();
        return inertia('News/Show', compact('post'));
    }
}
