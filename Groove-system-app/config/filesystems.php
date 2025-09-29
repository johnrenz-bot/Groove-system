<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

                'community_media' => [
                'driver' => 'local',
                'root' => storage_path('app/public/community/posts'),
                'url' => env('APP_URL') . '/storage/community/posts',
                'visibility' => 'public',
            ],


                    'client_media' => [
                    'driver' => 'local',
                    'root' => storage_path('app/public/client/posts'),
                    'url' => env('APP_URL') . '/storage/client/posts',
                    'visibility' => 'public',
                ],

                'coach_media' => [
                'driver' => 'local',
                'root' => storage_path('app/public/coach/posts'), 
                'url' => env('APP_URL') . '/storage/coach/posts',  
                'visibility' => 'public',
            ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),

        // ✅ No need to link this separately if it's already inside app/public
        // public_path('community') => storage_path('app/public/community'),
    ],

];
