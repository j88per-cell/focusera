<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        $posts = NewsPost::orderByDesc('created_at')->paginate(15);
        return inertia('Admin/News/Index', compact('posts'));
    }

    public function create()
    {
        return inertia('Admin/News/Edit', ['post' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'published_at' => 'nullable|date',
        ]);
        $post = new NewsPost($data);
        $post->author_id = optional($request->user())->id;
        $post->save();
        return redirect()->route('admin.news.index');
    }

    public function edit(NewsPost $news)
    {
        return inertia('Admin/News/Edit', ['post' => $news]);
    }

    public function update(Request $request, NewsPost $news)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'published_at' => 'nullable|date',
        ]);
        $news->update($data);
        return redirect()->route('admin.news.index');
    }

    public function destroy(NewsPost $news)
    {
        $news->delete();
        return redirect()->route('admin.news.index');
    }
}

