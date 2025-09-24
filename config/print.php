<?php

return [
    // Global sandbox flag (overridden by settings.sales.sandbox if present)
    'sandbox' => env('PRINT_SANDBOX', true),

    // Registered providers. Key used in settings: sales.provider
    'providers' => [
        'null' => App\Print\Providers\NullProvider::class,
        'artelo' => App\Print\Providers\ArteloProvider::class,
        'pictorem' => App\Print\Providers\PictoremProvider::class,
        'lumaprints' => App\Print\Providers\LumaprintsProvider::class,
        'finerworks' => App\Print\Providers\FinerWorksProvider::class,
    ],

    // Optional per-provider options (e.g., endpoints, credentials keys)
    'options' => [
        // Example provider configs (fill envs when adding real providers)
        'artelo' => [
            'endpoint' => [
                'sandbox' => env('ARTELO_SANDBOX_URL', null),
                'live' => env('ARTELO_LIVE_URL', null),
            ],
            'api_key' => env('ARTELO_API_KEY'),
            'api_secret' => env('ARTELO_API_SECRET'),
        ],
        'pictorem' => [
            'endpoint' => [
                'sandbox' => env('PICTOREM_SANDBOX_URL', null),
                'live' => env('PICTOREM_LIVE_URL', null),
            ],
            'api_key' => env('PICTOREM_API_KEY'),
            'api_secret' => env('PICTOREM_API_SECRET'),
        ],
        'lumaprints' => [
            'endpoint' => [
                'sandbox' => env('LUMAPRINTS_SANDBOX_URL', null),
                'live' => env('LUMAPRINTS_LIVE_URL', null),
            ],
            'api_key' => env('LUMAPRINTS_API_KEY'),
            'api_secret' => env('LUMAPRINTS_API_SECRET'),
        ],
        'finerworks' => [
            'endpoint' => [
                'sandbox' => env('FINERWORKS_SANDBOX_URL', null),
                'live' => env('FINERWORKS_LIVE_URL', null),
            ],
            'api_key' => env('FINERWORKS_API_KEY'),
            'api_secret' => env('FINERWORKS_API_SECRET'),
        ],
    ],
];
