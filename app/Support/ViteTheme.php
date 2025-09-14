// app/Support/ViteTheme.php
<?php

namespace App\Support;

class ViteTheme
{
    protected static function manifest(): array
    {
        $file = public_path('build/manifest.json');
        if (!is_file($file)) return [];
        return json_decode(file_get_contents($file), true) ?? [];
    }

    public static function tagsForTheme(string $theme): string
    {
        $manifest = self::manifest();
        $entry = "resources/js/themes/{$theme}/main.js";
        if (!isset($manifest[$entry])) return '';

        $html = [];

        // CSS first (from the JS entry)
        foreach ($manifest[$entry]['css'] ?? [] as $css) {
            $html[] = '<link rel="stylesheet" href="/'.$css.'">';
        }

        // Also include theme-specific CSS if you keep it separate
        $cssEntry = "resources/css/themes/{$theme}.css";
        if (isset($manifest[$cssEntry])) {
            $html[] = '<link rel="stylesheet" href="/'.$manifest[$cssEntry]['file'].'">';
        }

        // JS module
        $html[] = '<script type="module" src="/'.$manifest[$entry]['file'].'"></script>';

        return implode("\n", $html);
    }

    public static function tagsForAdmin(): string
    {
        $m = self::manifest();
        $html = [];

        if (isset($m['resources/css/admin.css'])) {
            $html[] = '<link rel="stylesheet" href="/'.$m['resources/css/admin.css']['file'].'">';
        }
        if (isset($m['resources/js/admin/main.js'])) {
            $html[] = '<script type="module" src="/'.$m['resources/js/admin/main.js']['file'].'"></script>';
        }
        return implode("\n", $html);
    }
}

