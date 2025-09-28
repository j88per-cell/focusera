<?php

namespace App\Http\Middleware;

use App\Support\Analytics;
use Closure;
use Illuminate\Http\Request;

class TrackAnalytics
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldTrack($request)) {
            Analytics::record('page_view', [], $request);
        }

        return $response;
    }

    protected function shouldTrack(Request $request): bool
    {
        if (!Analytics::enabled()) {
            return false;
        }

        if (!$request->isMethod('GET')) {
            return false;
        }

        if ($request->expectsJson()) {
            return false;
        }

        $path = trim($request->path(), '/');
        if (str_starts_with($path, 'admin')) {
            return false;
        }

        if (str_starts_with($path, 'storage')) {
            return false;
        }

        if (str_starts_with($path, 'media/photos')) {
            return false;
        }

        return true;
    }
}
