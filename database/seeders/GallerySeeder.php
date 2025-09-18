<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\GalleryImportService;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $baseDir = storage_path('app/private');
        app(GalleryImportService::class)->import($baseDir);
    }
}
