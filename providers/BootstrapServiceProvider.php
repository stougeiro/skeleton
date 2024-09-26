<?php declare(strict_types=1);

    namespace STDW\Providers;

    use STDW\Contract\ServiceProviderAbstracted;


    class BootstrapServiceProvider extends ServiceProviderAbstracted
    {
        public function register(): void
        { }

        public function boot(): void
        { }

        public function terminate(): void
        { }
    }