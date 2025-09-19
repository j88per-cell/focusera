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
    'upload_queue_clear_seconds' => env('PHOTOS_UPLOAD_QUEUE_CLEAR_SECONDS', 15),
    'chunk_bytes' => env('PHOTOS_CHUNK_BYTES', 5 * 1024 * 1024), // 5MB default

    // Derivative sizing (longest edge)
    'web_max_px' => env('PHOTOS_WEB_MAX_PX', 800),
    'thumb_max_px' => env('PHOTOS_THUMB_MAX_PX', 400),

    // Normalized EXIF keys to commonly displayed fields
    'exif_map' => [
        'camera'   => ['Model', 'EXIF\Model', 'MakeModel', 'Make Model'],
        'lens'     => ['LensModel', 'EXIF\LensModel', 'LensID', 'Lens'],
        'aperture' => ['FNumber', 'EXIF\FNumber'],
        'shutter'  => ['ExposureTime', 'EXIF\ExposureTime'],
        'iso'      => ['ISO', 'EXIF\ISO'],
        'focal'    => ['FocalLength', 'EXIF\FocalLength'],
        'datetime' => [
            'DateTimeOriginal', 'CreateDate', 'DateTime',
            'EXIF\DateTimeOriginal', 'QuickTime\CreateDate', 'Composite\SubSecDateTimeOriginal'
        ],
        'photographer' => ['Artist', 'IFD0\Artist', 'Creator', 'By-line', 'XMP-dc:Creator'],
    ],
];
