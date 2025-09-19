<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use Inertia\Inertia;

class GalleryController extends Controller
{
    // Admin: list + quick create view (theme-based UI)
    public function adminIndex()
    {
        $galleries = Gallery::withCount('photos')->latest()->paginate(20);
        $parents = Gallery::orderBy('title')->get(['id','title']);
        return inertia('Admin/Galleries/Index', compact('galleries','parents'));
    }

    public function index()
    {
        $galleries = Gallery::latest()->paginate(12);
        return inertia('Gallery/Index', compact('galleries'));
    }

    public function create()
    {
        return inertia('Gallery/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'public' => 'boolean',
            'access_code' => 'nullable|string|max:20',
            'thumbnail' => 'required|string',
        ]);

        Gallery::create($data);
        return redirect()->route('galleries.index');
    }

    public function show(Gallery $gallery)
    {
        $gallery->load('photos');
        return inertia('Gallery/Show', compact('gallery'));
    }

    public function edit(Gallery $gallery)
    {
        $parents = Gallery::where('id', '!=', $gallery->id)->orderBy('title')->get(['id','title']);
        $photos = $gallery->photos()->latest()->paginate(40, ['id','title','description','path_thumb','path_web']);
        return inertia('Galleries/Edit', [
            'gallery' => $gallery,
            'parents' => $parents,
            'photos'  => $photos,
        ]);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'public' => 'boolean',
            'access_code' => 'nullable|string|max:20',
            'thumbnail' => 'required|string',
        ]);

        $gallery->update($data);
        return redirect()->route('galleries.index');
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();
        return redirect()->route('galleries.index');
    }
}
