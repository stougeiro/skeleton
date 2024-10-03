<?php declare(strict_types=1);

    use STDW\Providers\RouterServiceProvider;
    use STDW\Providers\BootstrapServiceProvider;
    use STDW\Cache\ServiceProvider as CacheServiceProvider;
    use STDW\View\ServiceProvider as ViewServiceProvider;


    return
    [
        ViewServiceProvider::class,
        CacheServiceProvider::class,
        RouterServiceProvider::class,
        BootstrapServiceProvider::class,
    ];