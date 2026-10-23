<?php declare(strict_types=1);

use App\Provider\ErrorServiceProvider;
use App\Provider\CacheServiceProvider;
use App\Provider\ViewServiceProvider;
use App\Provider\HttpServiceProvider;
use App\Provider\AppServiceProvider;

use App\Error\ErrorModuleProvider;
use App\HelloWorld\HelloWorldModuleProvider;


return
[
    'environment' => env('environment', 'development'),

    'providers' => [
        ErrorServiceProvider::class,
        CacheServiceProvider::class,
        ViewServiceProvider::class,
        HttpServiceProvider::class,
        AppServiceProvider::class,
    ],

    'modules' => [
        ErrorModuleProvider::class,
        HelloWorldModuleProvider::class,
    ],
];
