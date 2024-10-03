<?php declare(strict_types=1);

    return
    [
        'name' => config('app.name'),

        'save_path' => storage_path('session'),

        'cookie' =>
        [
            'lifetime' => 0, 

            'secure' => false, // https only

            'same_site' => 'Lax', // Supported: Lax, Strict, None
        ],

        'garbage_collector' =>
        [
            'maxlifetime' => 1800, // 30min

            'probability' => 1,

            'divisor' => 100,
        ],

        'extra' =>
        [
            'regeneration' => false,

            'regeneration_time' => 1200, // 20min
        ],
    ];