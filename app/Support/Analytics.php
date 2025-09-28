<?php

namespace App\Support;

use App\Jobs\ProcessAnalyticsEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Analytics
{
    public static function enabled(): bool
    {
        return filter_var(config('site.analytics.enabled', false), FILTER_VALIDATE_BOOL);
    }

    public static function record(string $event, array $meta = [], ?Request $request = null): void
    {
        if (!self::enabled()) {
            return;
        }

        $request ??= request();
        if (!$request) {
            return;
        }

        $session = $request->session();
        if (!$session || !$session->isStarted()) {
            return;
        }

        $sessionId = $session->getId();
        if (!$sessionId) {
            return;
        }

        $timestamp = CarbonImmutable::now();
        $user = $request->user();

        $referrerHost = self::shouldCaptureReferrer() ? self::referrerHost($request->headers->get('referer')) : null;

        if ($referrerHost && !isset($meta['referrer'])) {
            $meta['referrer'] = $referrerHost;
        }

        $payload = [
            'session_id' => $sessionId,
            'user_id' => $user?->id,
            'tenant_id' => null,
            'event' => $event,
            'meta' => self::buildMeta($request, $meta),
            'device' => self::detectDevice($request->userAgent()),
            'referrer' => $referrerHost,
            'timestamp' => $timestamp,
        ];

        ProcessAnalyticsEvent::dispatch($payload)->onQueue(config('site.analytics.queue', 'default'));
    }

    public static function recordPrivateGalleryView(int $galleryId, ?int $accessCodeId = null, array $meta = [], ?Request $request = null): void
    {
        $meta = array_merge([
            'gallery_id' => $galleryId,
            'access_code_id' => $accessCodeId,
        ], $meta);

        self::record('private_gallery_view', $meta, $request);
    }

    public static function recordConversion(array $meta = [], ?Request $request = null): void
    {
        self::record('conversion', $meta, $request);
    }

    protected static function buildMeta(Request $request, array $meta): array
    {
        $path = $request->getPathInfo();
        $normalized = self::normalizePath($path);
        $routeName = optional($request->route())->getName();

        $base = [
            'path' => $path,
            'normalized_path' => $normalized,
            'route' => $routeName,
            'method' => $request->getMethod(),
        ];

        if (!Arr::has($meta, 'gallery_id')) {
            $gallery = optional($request->route())->parameter('gallery');
            if (is_object($gallery) && isset($gallery->id)) {
                $base['gallery_id'] = $gallery->id;
            } elseif (is_numeric($gallery)) {
                $base['gallery_id'] = (int) $gallery;
            }
        }

        if (!Arr::has($meta, 'photo_id')) {
            $photo = optional($request->route())->parameter('photo');
            if (is_object($photo) && isset($photo->id)) {
                $base['photo_id'] = $photo->id;
            } elseif (is_numeric($photo)) {
                $base['photo_id'] = (int) $photo;
            }
        }

        return array_filter(array_merge($base, $meta), function ($value) {
            return !is_null($value);
        });
    }

    protected static function normalizePath(string $path): string
    {
        $trimmed = trim($path, '/');
        if ($trimmed === '') {
            return '/';
        }

        $segments = explode('/', $trimmed);
        $normalized = array_map(function ($segment) {
            if (is_numeric($segment)) {
                return '{id}';
            }
            if (Str::isUuid($segment)) {
                return '{uuid}';
            }
            return $segment;
        }, $segments);

        return '/' . implode('/', $normalized);
    }

    protected static function detectDevice(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'other';
        }

        $ua = Str::lower($userAgent);
        $tabletKeywords = ['tablet', 'ipad', 'playbook', 'kindle'];
        $mobileKeywords = ['iphone', 'android', 'blackberry', 'webos', 'mobile', 'phone'];

        foreach ($tabletKeywords as $keyword) {
            if (Str::contains($ua, $keyword)) {
                return 'tablet';
            }
        }

        foreach ($mobileKeywords as $keyword) {
            if (Str::contains($ua, $keyword)) {
                return 'mobile';
            }
        }

        return 'desktop';
    }

    protected static function referrerHost(?string $referrer): ?string
    {
        if (!$referrer) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        return $host ?: null;
    }

    protected static function shouldCaptureReferrer(): bool
    {
        return filter_var(config('site.analytics.capture_referrer', true), FILTER_VALIDATE_BOOL);
    }
}
