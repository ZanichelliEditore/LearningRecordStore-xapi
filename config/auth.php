<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'oauthclients',
    ],

    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'oauthclients',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\User::class,
        ],
        'clients' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Client::class,
        ],
        'oauthclients' => [
            'driver' => 'eloquent',
            'model'  => App\Models\OauthClient::class,
        ],
    ],

    'passwords' => [
        'oauthclients' => [
            'provider' => 'oauthclients',
            'table' => 'oauth_refresh_tokens',
            'expire' => 60,
        ],
    ],
];
