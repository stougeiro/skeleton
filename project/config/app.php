<?php declare(strict_types=1);

use App\Provider\CacheServiceProvider;
use App\Provider\ViewServiceProvider;
use App\Provider\HttpServiceProvider;
use App\Provider\PipelineServiceProvider;
use App\Provider\AppServiceProvider;

use App\Error\ErrorModuleProvider;
use App\HelloWorld\HelloWorldModuleProvider;


return
[
    'environment' => env('environment', 'development'),

    'providers' => [
        CacheServiceProvider::class,
        ViewServiceProvider::class,
        HttpServiceProvider::class,
        PipelineServiceProvider::class,
        AppServiceProvider::class,
    ],

    'modules' => [
        ErrorModuleProvider::class,
        HelloWorldModuleProvider::class,
    ],
];
