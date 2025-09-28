<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\Gallery;
use App\Models\Photo;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index() {
        $analyticsEnabled = filter_var(config('site.analytics.enabled', false), FILTER_VALIDATE_BOOL);

        $analytics = $analyticsEnabled ? $this->buildAnalyticsSummary() : null;

        return Inertia::render('Dashboard', [
            'analyticsEnabled' => $analyticsEnabled,
            'analytics' => $analytics,
        ])->rootView('admin');
    }

    protected function buildAnalyticsSummary(): array
    {
        $now = Carbon::now();
        $periodStart = $now->copy()->subDays(30);

        $sessions = AnalyticsSession::where('last_seen', '>=', $periodStart)->get(['session_id', 'first_seen', 'last_seen', 'device']);
        $sessionCount = $sessions->count();

        $events = AnalyticsEvent::where('created_at', '>=', $periodStart)->get(['session_id', 'event', 'meta', 'created_at']);

        $pageViews = $events->where('event', 'page_view')->count();
        $privateViews = $events->where('event', 'private_gallery_view')->count();
        $conversions = $events->where('event', 'conversion')->count();

        $dailyWindow = $now->copy()->subDays(13)->startOfDay();
        $dailyBuckets = [];
        for ($i = 0; $i < 14; $i++) {
            $date = $dailyWindow->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $dailyBuckets[$key] = [
                'label' => $date->format('M j'),
                'page_view' => 0,
                'private_gallery_view' => 0,
                'conversion' => 0,
            ];
        }

        foreach ($events as $event) {
            $dayKey = Carbon::parse($event->created_at)->format('Y-m-d');
            if (!isset($dailyBuckets[$dayKey])) {
                continue;
            }
            if (isset($dailyBuckets[$dayKey][$event->event])) {
                $dailyBuckets[$dayKey][$event->event] += 1;
            }
        }

        $sessionsByEvent = $events->groupBy('session_id');
        $bounceSessions = $sessionsByEvent->filter(fn ($collection) => $collection->count() === 1)->count();
        $bounceRate = $sessionCount > 0 ? round(($bounceSessions / $sessionCount) * 100, 1) : 0;

        $avgDuration = $sessions->map(function ($session) {
            if (!$session->first_seen || !$session->last_seen) {
                return 0;
            }
            return Carbon::parse($session->first_seen)->diffInSeconds(Carbon::parse($session->last_seen));
        })->filter()->avg();
        $avgDuration = $avgDuration ? round($avgDuration / 60, 1) : 0; // minutes

        $deviceSplit = $sessions->groupBy(function ($session) {
            return $session->device ?: 'other';
        })->map->count()->toArray();

        $deviceSplit = array_merge([
            'desktop' => 0,
            'mobile' => 0,
            'tablet' => 0,
            'other' => 0,
        ], $deviceSplit);

        $topGalleries = $this->aggregateTopEntities($events, 'gallery_id', function ($ids) {
            return Gallery::whereIn('id', $ids)->pluck('title', 'id');
        }, 'Gallery #');

        $topPhotos = $this->aggregateTopEntities($events, 'photo_id', function ($ids) {
            return Photo::whereIn('id', $ids)->pluck('title', 'id');
        }, 'Photo #');

        return [
            'summary' => [
                'sessions' => $sessionCount,
                'page_views' => $pageViews,
                'private_views' => $privateViews,
                'conversions' => $conversions,
                'bounce_rate' => $bounceRate,
                'avg_session_minutes' => $avgDuration,
            ],
            'events_by_day' => array_values($dailyBuckets),
            'device_split' => $deviceSplit,
            'top_galleries' => $topGalleries,
            'top_photos' => $topPhotos,
        ];
    }

    protected function aggregateTopEntities(Collection $events, string $key, ?callable $labelResolver = null, string $defaultPrefix = 'ID '): array
    {
        $counts = [];
        foreach ($events as $event) {
            $meta = $event->meta ?? [];
            if (!array_key_exists($key, $meta)) {
                continue;
            }
            $id = $meta[$key];
            if (!$id) {
                continue;
            }
            $counts[$id] = ($counts[$id] ?? 0) + 1;
        }

        arsort($counts);
        $top = array_slice($counts, 0, 5, true);

        $labels = $labelResolver ? $labelResolver(array_keys($top)) : [];

        $results = [];
        foreach ($top as $id => $count) {
            $label = $labels[$id] ?? null;
            if (!$label) {
                $label = rtrim($defaultPrefix) . ' ' . $id;
            }
            $results[] = [
                'id' => $id,
                'label' => $label,
                'count' => $count,
            ];
        }

        return $results;
    }
}
