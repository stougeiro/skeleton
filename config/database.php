<?php declare(strict_types=1);

    return
    [
        'connection' =>
        [
            'default' =>
            [
                'driver' => env('database.default.driver'),
                'host' => env('database.default.host'),
                'port' => env('database.default.port'),
                'schema' => env('database.default.schema'),
                'username' => env('database.default.username'),
                'password' => env('database.default.password'),
            ],
        ],
    ];