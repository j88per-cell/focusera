<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GalleryImportService;

class ImportGalleries extends Command
{
    protected $signature = 'galleries:import {base? : Base directory (defaults to storage/app/private)}';
    protected $description = 'Scan subfolders and import galleries and photos with EXIF';

    public function handle(GalleryImportService $service): int
    {
        $base = $this->argument('base') ?: storage_path('app/private');
        $this->info("Importing galleries from: {$base}");

        $result = $service->import($base);

        $this->info("Imported {$result['galleries']} galleries and {$result['photos']} photos.");
        return self::SUCCESS;
    }
}

