<?php

namespace App\Support;

use App\Models\Photo;
use App\Models\Gallery;

class Pricing
{
    public static function resolveMarkupPercent(?Photo $photo = null, ?Gallery $gallery = null): float
    {
        if ($photo && $photo->markup_percent !== null) return (float) $photo->markup_percent;
        if ($gallery && $gallery->markup_percent !== null) return (float) $gallery->markup_percent;
        $default = config('settings.sales.markup_percent');
        return $default !== null ? (float) $default : 25.0;
    }

    public static function applyMarkup(float $base, float $percent): float
    {
        return round($base * (1.0 + $percent / 100.0), 2);
    }
}

