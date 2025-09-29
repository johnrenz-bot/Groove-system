<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openrouter' => [
        'key'     => env('OPENROUTER_API_KEY'),
        'base'    => env('OPENROUTER_BASE', 'https://openrouter.ai/api/v1'),
        'model'   => env('OPENROUTER_MODEL', 'deepseek/deepseek-v3.1'),
        'referer' => env('OPENROUTER_REFERER', ''),
        'title'   => env('OPENROUTER_TITLE', 'GrooveAI'),
    ],
];
