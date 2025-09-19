<?php

return [
    'storage' => [
        // Publicly served derivatives
        'web_root'   => 'storage/gallery/web',
        'thumb_root' => 'storage/gallery/thumbnails',
        // Originals, private
        'originals_root' => 'storage/app/private',
    ],

    'allowed_extensions' => ['jpg', 'jpeg', 'png'],
    'max_upload_mb' => env('PHOTOS_MAX_UPLOAD_MB', 100),
    'max_execution_seconds' => env('PHOTOS_MAX_EXEC_SECONDS', 300),

    // Normalized EXIF keys to commonly displayed fields
    'exif_map' => [
        'camera'   => ['Model', 'EXIF\Model', 'MakeModel', 'Make Model'],
        'lens'     => ['LensModel', 'EXIF\LensModel', 'LensID', 'Lens'],
        'aperture' => ['FNumber', 'EXIF\FNumber'],
        'shutter'  => ['ExposureTime', 'EXIF\ExposureTime'],
        'iso'      => ['ISO', 'EXIF\ISO'],
        'focal'    => ['FocalLength', 'EXIF\FocalLength'],
    ],
];
