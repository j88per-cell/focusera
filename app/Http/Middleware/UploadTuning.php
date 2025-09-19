<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UploadTuning
{
    public function handle(Request $request, Closure $next)
    {
        $seconds = (int) config('photos.max_execution_seconds', 300);
        if ($seconds > 0) {
            @set_time_limit($seconds);
            @ini_set('max_execution_time', (string) $seconds);
            @ini_set('max_input_time', (string) $seconds);
        }
        // Note: upload_max_filesize and post_max_size cannot be reliably changed at runtime.
        return $next($request);
    }
}

