<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\GalleryAccessCode;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    // Admin: list + quick create view (theme-based UI)
    public function adminIndex()
    {
        $galleries = Gallery::withCount('photos')->latest()->paginate(20);
        $parents = Gallery::orderBy('title')->get(['id','title']);
        return inertia('Galleries/Index', compact('galleries','parents'))->rootView('admin');
    }

    public function index()
    {
        // Public listing: show only top-level galleries
        $query = Gallery::query()->withCount(['photos', 'children']);
        if (!auth()->check() || !auth()->user()->can('isAdmin')) {
            $query->where('public', true);
        }

        $galleries = $query
            ->whereNull('parent_id')
            ->orderBy('title')
            ->paginate(12);

        $galleries->getCollection()->transform(function (Gallery $gallery) {
            $thumb = $this->resolvePublicPath($gallery->thumbnail);
            return [
                'id' => $gallery->id,
                'title' => $gallery->title,
                'description' => $gallery->description,
                'attribution' => $gallery->attribution,
                'notes' => $gallery->notes,
                'thumbnail' => $thumb,
                'thumb_url' => $thumb,
                'photos_count' => $gallery->photos_count ?? 0,
                'children_count' => $gallery->children_count ?? 0,
            ];
        });

        return inertia('Gallery/Index', ['galleries' => $galleries]);
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
            'attribution' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
            'public' => 'boolean',
            'allow_orders' => 'boolean',
            'markup_percent' => 'nullable|numeric|min:0|max:1000',
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
                'web_url' => $p->web_url,
                'thumb_url' => $p->thumb_url,
                'attribution' => $p->attribution,
                'notes' => $p->notes,
                'exif' => \App\Support\ExifHelper::filterForGallery($gallery, (array) ($p->exif ?? [])),
            ];
        });

        $childQuery = $gallery->children()->withCount(['photos', 'children']);
        if (!auth()->check() || !auth()->user()->can('isAdmin')) {
            $childQuery->where('public', true);
        }

        $childGalleries = $childQuery->orderBy('title')->get()->map(function (Gallery $child) {
            $thumb = $this->resolvePublicPath($child->thumbnail);
            return [
                'id' => $child->id,
                'title' => $child->title,
                'description' => $child->description,
                'attribution' => $child->attribution,
                'notes' => $child->notes,
                'thumbnail' => $thumb,
                'thumb_url' => $thumb,
                'photos_count' => $child->photos_count ?? 0,
                'children_count' => $child->children_count ?? 0,
            ];
        });

        $galleryData = [
            'id' => $gallery->id,
            'title' => $gallery->title,
            'description' => $gallery->description,
            'attribution' => $gallery->attribution,
            'notes' => $gallery->notes,
            'date' => $gallery->date,
            'public' => $gallery->public,
            'child_galleries' => $childGalleries,
            'photos' => $photos,
        ];
        return inertia('Gallery/Show', ['gallery' => $galleryData]);
    }

    public function edit(Gallery $gallery)
    {
        $this->authorize('update', $gallery);
        $parents = Gallery::where('id', '!=', $gallery->id)->orderBy('title')->get(['id','title']);
        $photos = $gallery->photos()->latest()->paginate(40, ['id','title','description','attribution','notes','path_thumb','path_web','exif','markup_percent','updated_at']);
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
        ])->rootView('admin');
    }

    public function update(Request $request, Gallery $gallery)
    {
        $this->authorize('update', $gallery);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'attribution' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
            'public' => 'boolean',
            'allow_orders' => 'boolean',
            'markup_percent' => 'nullable|numeric|min:0|max:1000',
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

    protected function resolvePublicPath(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        if (Str::startsWith($value, '/')) {
            return $value;
        }

        if (Str::startsWith($value, 'storage/')) {
            return '/' . ltrim($value, '/');
        }

        try {
            return Storage::disk(config('site.storage.public_disk', 'photos_public'))->url($value);
        } catch (\Throwable $e) {
            return $value;
        }
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

    // Public: access code entry form
    public function accessForm(Request $request)
    {
        return inertia('Gallery/Access');
    }

    // Public: process access code and redirect to the matched gallery
    public function accessSubmit(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|min:4|max:64',
        ]);

        $codeInput = trim($data['code']);
        $now = now();

        // Try generated codes (hashed) first
        $candidates = GalleryAccessCode::query()
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->with('gallery')
            ->get();
        foreach ($candidates as $cand) {
            if (Hash::check($codeInput, $cand->code_hash)) {
                $gallery = $cand->gallery;
                if ($gallery) {
                    $request->session()->put('gallery.access.' . $gallery->id, [
                        'code_id' => $cand->id,
                        'granted_at' => $now->toISOString(),
                    ]);
                    return redirect()->to(route('galleries.show', $gallery));
                }
            }
        }

        // Legacy fallback: plaintext code on Gallery
        $legacy = Gallery::query()->where('access_code', $codeInput)->first();
        if ($legacy) {
            $request->session()->put('gallery.access.' . $legacy->id, true);
            return redirect()->to(route('galleries.show', $legacy));
        }

        return back()->withErrors(['code' => 'Invalid or expired access code.'])->withInput();
    }
}
