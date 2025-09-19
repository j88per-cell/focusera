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
            'thumbnail' => 'nullable|string',
            'parent_id' => 'nullable|exists:galleries,id',
            'exif_visibility' => 'nullable|in:all,none,custom',
            'exif_fields' => 'nullable|array',
        ]);

        Gallery::create($data);
        $target = auth()->check() && auth()->user()->can('isAdmin')
            ? route('admin.galleries.index')
            : route('galleries.index');
        return redirect($target);
    }

    public function show(Gallery $gallery)
    {
        $gallery->load('photos');
        // Build filtered EXIF per gallery settings for public display
        $photos = $gallery->photos->map(function ($p) use ($gallery) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'description' => $p->description,
                'path_web' => $p->path_web,
                'path_thumb' => $p->path_thumb,
                'exif' => \App\Support\ExifHelper::filterForGallery($gallery, (array) ($p->exif ?? [])),
            ];
        });
        $galleryData = [
            'id' => $gallery->id,
            'title' => $gallery->title,
            'description' => $gallery->description,
            'date' => $gallery->date,
            'public' => $gallery->public,
            'photos' => $photos,
        ];
        return inertia('Gallery/Show', ['gallery' => $galleryData]);
    }

    public function edit(Gallery $gallery)
    {
        $parents = Gallery::where('id', '!=', $gallery->id)->orderBy('title')->get(['id','title']);
        $photos = $gallery->photos()->latest()->paginate(40, ['id','title','description','path_thumb','path_web','exif']);
        // Filter EXIF for admin display as well (honor gallery settings)
        $photos->getCollection()->transform(function ($p) use ($gallery) {
            $p->exif = \App\Support\ExifHelper::filterForGallery($gallery, (array) ($p->exif ?? []));
            return $p;
        });
        return inertia('Galleries/Edit', [
            'gallery' => $gallery,
            'parents' => $parents,
            'photos'  => $photos,
            'uploadMaxMb' => (int) config('photos.max_upload_mb', 100),
            'uploadQueueClearSeconds' => (int) config('photos.upload_queue_clear_seconds', 15),
            'chunkBytes' => (int) config('photos.chunk_bytes', 5 * 1024 * 1024),
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
            'thumbnail' => 'nullable|string',
            'parent_id' => 'nullable|exists:galleries,id',
            'exif_visibility' => 'nullable|in:all,none,custom',
            'exif_fields' => 'nullable|array',
        ]);

        $gallery->update($data);
        $target = auth()->check() && auth()->user()->can('isAdmin')
            ? route('admin.galleries.index')
            : route('galleries.index');
        return redirect($target);
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();
        $target = auth()->check() && auth()->user()->can('isAdmin')
            ? route('admin.galleries.index')
            : route('galleries.index');
        return redirect($target);
    }
}
