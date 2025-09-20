<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\GalleryAccessCode;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        // Public listing: only show public galleries with no access code
        $query = Gallery::query();
        if (!auth()->check() || !auth()->user()->can('isAdmin')) {
            $query->where('public', true)
                  ->where(function ($q) {
                      $q->whereNull('access_code')->orWhere('access_code', '');
                  })
                  ->whereDoesntHave('accessCodes', function ($q) {
                      $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                  });
        }
        $galleries = $query->latest()->paginate(12);
        return inertia('Gallery/Index', compact('galleries'));
    }

    public function create()
    {
        $this->authorize('create', \App\Models\Gallery::class);
        return inertia('Gallery/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Gallery::class);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'public' => 'boolean',
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

    public function show(Request $request, Gallery $gallery)
    {
        // Access-code flow: if a code query is provided, attempt unlock.
        if ($request->has('code')) {
            $codeInput = (string) $request->query('code');
            $sessionKey = 'gallery.access.' . $gallery->id;

            // Prefer multi-code model (hashed). Accept legacy plain code as fallback.
            try {
                $now = now();
                $validCodes = \App\Models\GalleryAccessCode::query()
                    ->where('gallery_id', $gallery->id)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
                    })
                    ->get();
                $matched = null;
                foreach ($validCodes as $c) {
                    if (\Illuminate\Support\Facades\Hash::check($codeInput, $c->code_hash)) {
                        $matched = $c; break;
                    }
                }
                if ($matched) {
                    $request->session()->put($sessionKey, [
                        'code_id' => $matched->id,
                        'granted_at' => $now->toISOString(),
                    ]);
                    return redirect()->to(route('galleries.show', $gallery));
                }
            } catch (\Throwable $e) {
                // ignore and try legacy path
            }

            // Legacy single access_code on Gallery (plaintext compare)
            $expected = (string) ($gallery->access_code ?? '');
            if ($expected !== '' && hash_equals($expected, $codeInput)) {
                $request->session()->put($sessionKey, true);
                return redirect()->to(route('galleries.show', $gallery));
            }
        }

        $this->authorize('view', $gallery);

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
        $this->authorize('update', $gallery);
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
        $this->authorize('update', $gallery);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'public' => 'boolean',
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
        $this->authorize('delete', $gallery);
        $gallery->delete();
        $target = auth()->check() && auth()->user()->can('isAdmin')
            ? route('admin.galleries.index')
            : route('galleries.index');
        return redirect($target);
    }

    // Admin: generate a one-time (or time-limited) access code and email it
    public function adminGenerateCode(Request $request, Gallery $gallery)
    {
        $this->authorize('update', $gallery);

        $data = $request->validate([
            'email' => 'required|email',
            'duration' => 'required|string|in:infinite,7d,14d,30d,90d,1y',
            'label' => 'nullable|string|max:120',
        ]);

        $code = Str::upper(Str::random(8));
        $expiresAt = match ($data['duration']) {
            '7d' => now()->addDays(7),
            '14d' => now()->addDays(14),
            '30d' => now()->addDays(30),
            '90d' => now()->addDays(90),
            '1y' => now()->addYear(),
            default => null, // infinite
        };

        $record = GalleryAccessCode::create([
            'gallery_id' => $gallery->id,
            'code_hash' => Hash::make($code),
            'label' => $data['label'] ?? null,
            'expires_at' => $expiresAt,
        ]);

        $link = route('galleries.show', $gallery) . '?code=' . urlencode($code);

        try {
            Mail::to($data['email'])->send(new \App\Mail\GalleryAccessCodeMail($gallery, $code, $link, $expiresAt));
        } catch (\Throwable $e) {
            // Email failure shouldn’t lose the code; return the link and error
            return response()->json([
                'id' => $record->id,
                'code' => $code,
                'link' => $link,
                'expires_at' => $expiresAt?->toIso8601String(),
                'email_error' => $e->getMessage(),
            ], 201);
        }

        return response()->json([
            'id' => $record->id,
            'code' => $code,
            'link' => $link,
            'expires_at' => $expiresAt?->toIso8601String(),
        ], 201);
    }
}
