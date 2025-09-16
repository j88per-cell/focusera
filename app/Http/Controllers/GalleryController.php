<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::latest()->paginate(12);
        //dd($galleries);
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
        return inertia('Galleries/Show', compact('gallery'));
    }

    public function edit(Gallery $gallery)
    {
        return inertia('Galleries/Edit', compact('gallery'));
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
