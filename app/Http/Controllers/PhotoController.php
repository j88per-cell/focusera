<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\Photo;
use Inertia\Inertia;
use App\Services\PhotoProcessor;
use App\Services\ExifReader;
use App\Services\PhotoTransformer;

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
            'markup_percent' => 'nullable|numeric|min:0|max:1000',
        ]);

        $photo->update($data);
        return redirect()->back();
    }

    public function destroy(Gallery $gallery, Photo $photo)
    {
        $photo->delete();
        return redirect()->back();
    }

    public function upload(Request $request, Gallery $gallery)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png',
        ]);

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['message' => 'Invalid upload'], 422);
        }

        $tmpPath = $file->getRealPath();
        $originalName = $file->getClientOriginalName();

        try {
            $processor = new PhotoProcessor();
            $paths = $processor->import($gallery->id, $tmpPath, $originalName);
            $exif = (new ExifReader())->read($tmpPath);
        } catch (\Throwable $e) {
            return response()->json(['message' => $originalName . ': ' . $e->getMessage()], 422);
        }

        try {
            $photo = Photo::create([
                'gallery_id'   => $gallery->id,
                'title'        => pathinfo($originalName, PATHINFO_FILENAME),
                'description'  => null,
                'path_original'=> $paths['path_original'],
                'path_web'     => $paths['path_web'],
                'path_thumb'   => $paths['path_thumb'],
                'exif'         => $exif,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $originalName . ': ' . $e->getMessage()], 422);
        }

        return response()->json(['id' => $photo->id], 201);
    }

    public function transform(Request $request, Gallery $gallery, Photo $photo)
    {
        $data = $request->validate([
            'rotate' => 'nullable|integer|in:-180,-90,90,180',
            'flip'   => 'nullable|in:h,v',
        ]);

        try {
            (new PhotoTransformer())->transform($photo, $data);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Transform failed', 'error' => $e->getMessage()], 422);
        }

        return response()->noContent();
    }

    // Chunked upload endpoints
    public function chunkStart(Request $request, Gallery $gallery)
    {
        $request->validate(['name' => 'required|string']);
        $uploadId = bin2hex(random_bytes(16));
        $dir = storage_path('app/chunks/' . $uploadId);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . '/name.txt', $request->string('name'));
        return response()->json(['upload_id' => $uploadId]);
    }

    public function chunkUpload(Request $request, Gallery $gallery)
    {
        $request->validate([
            'upload_id' => 'required|string|size:32',
            'index' => 'required|integer|min:0',
            'total' => 'required|integer|min:1',
            'name'  => 'required|string',
            'chunk' => 'required|file',
        ]);

        $uploadId = $request->string('upload_id');
        $dir = storage_path('app/chunks/' . $uploadId);
        if (!is_dir($dir)) return response()->json(['message' => 'Invalid upload'], 400);

        $assembled = $dir . '/assembled.part';
        $chunkFile = $request->file('chunk');
        $stream = fopen($assembled, 'ab');
        $in = fopen($chunkFile->getRealPath(), 'rb');
        stream_copy_to_stream($in, $stream);
        fclose($in); fclose($stream);

        return response()->noContent();
    }

    public function chunkFinish(Request $request, Gallery $gallery)
    {
        $request->validate([
            'upload_id' => 'required|string|size:32',
        ]);
        $uploadId = $request->string('upload_id');
        $dir = storage_path('app/chunks/' . $uploadId);
        if (!is_dir($dir)) return response()->json(['message' => 'Invalid upload'], 400);

        $namePath = $dir . '/name.txt';
        $assembled = $dir . '/assembled.part';
        if (!file_exists($namePath) || !file_exists($assembled)) {
            return response()->json(['message' => 'Upload incomplete'], 400);
        }
        $name = trim((string) file_get_contents($namePath));
        try {
            $exif = (new ExifReader())->read($assembled);
        } catch (\Throwable $e) {
            return response()->json(['message' => $name . ': ' . $e->getMessage()], 422);
        }

        $processor = new PhotoProcessor();
        try {
            $paths = $processor->import($gallery->id, $assembled, $name);
        } catch (\Throwable $e) {
            return response()->json(['message' => $name . ': ' . $e->getMessage()], 422);
        }

        try {
            $photo = Photo::create([
                'gallery_id'   => $gallery->id,
                'title'        => pathinfo($name, PATHINFO_FILENAME),
                'description'  => null,
                'path_original'=> $paths['path_original'],
                'path_web'     => $paths['path_web'],
                'path_thumb'   => $paths['path_thumb'],
                'exif'         => $exif,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $name . ': ' . $e->getMessage()], 422);
        }

        // Cleanup chunks
        @unlink($assembled);
        @unlink($namePath);
        @rmdir($dir);

        return response()->json(['id' => $photo->id], 201);
    }
}
