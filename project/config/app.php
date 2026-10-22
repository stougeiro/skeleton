<?php declare(strict_types=1);

use App\Provider\AppServiceProvider;
use App\Provider\CacheServiceProvider;
use App\Provider\HttpServiceProvider;
use App\Provider\ViewServiceProvider;

use App\Error\ErrorModuleProvider;
use App\HelloWorld\HelloWorldModuleProvider;


return [
    'providers' => [
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
