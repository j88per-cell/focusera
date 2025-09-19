<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\Photo;
use Inertia\Inertia;

class PhotoController extends Controller
{
    public function index(Gallery $gallery)
    {
        $photos = $gallery->photos()->paginate(24);
        return inertia('Photos/Index', compact('gallery','photos'));
    }

    public function create(Gallery $gallery)
    {
        return inertia('Photos/Create', compact('gallery'));
    }

    public function store(Request $request, Gallery $gallery)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'exif' => 'nullable|json',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'path' => 'required|string',
        ]);

        $gallery->photos()->create($data);
        return redirect()->route('galleries.photos.index', $gallery);
    }

    public function show(Gallery $gallery, Photo $photo)
    {
        return inertia('Photos/Show', compact('gallery','photo'));
    }

    public function edit(Gallery $gallery, Photo $photo)
    {
        return inertia('Photos/Edit', compact('gallery','photo'));
    }

    public function update(Request $request, Gallery $gallery, Photo $photo)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'exif' => 'nullable|array',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'path_original' => 'nullable|string',
            'path_web' => 'nullable|string',
            'path_thumb' => 'nullable|string',
        ]);

        $photo->update($data);
        return redirect()->back();
    }

    public function destroy(Gallery $gallery, Photo $photo)
    {
        $photo->delete();
        return redirect()->back();
    }
}
