<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        $enabled = (bool) (config("features.$feature") ?? config("settings.features.$feature") ?? false);
        if (!$enabled) {
            abort(404);
        }
        return $next($request);
    }
}

