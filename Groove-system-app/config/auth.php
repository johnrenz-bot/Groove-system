<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        'client' => [
            'driver'   => 'session',
            'provider' => 'clients',
        ],

        'coach' => [
            'driver'   => 'session',
            'provider' => 'coaches',
        ],

        'admin' => [
            'driver'   => 'session',
            'provider' => 'admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],

        'clients' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Client::class,
        ],

        'coaches' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Coach::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset Passwords (Password Brokers)
    |--------------------------------------------------------------------------
    | If you’re handling resets manually (as in your LoginController), this
    | section can be left unused. Keeping it configured doesn’t hurt.
    |
    | For Laravel 10+, the default table is `password_reset_tokens`.
    | If you intentionally use your old `password_resets` table, switch the
    | "table" value below accordingly.
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens', // or 'password_resets'
            'expire'   => 60,
            'throttle' => 60,
        ],

        'clients' => [
            'provider' => 'clients',
            'table'    => 'password_reset_tokens', // or 'password_resets'
            'expire'   => 60,
            'throttle' => 60,
        ],

        'coaches' => [
            'provider' => 'coaches',
            'table'    => 'password_reset_tokens', // or 'password_resets'
            'expire'   => 60,
            'throttle' => 60,
        ],

        // Optional but nice to have for Admin
        'admins' => [
            'provider' => 'admins',
            'table'    => 'password_reset_tokens', // or 'password_resets'
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */
    'password_timeout' => 10800,

];
