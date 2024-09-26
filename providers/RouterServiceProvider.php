<?php declare(strict_types=1);

    namespace STDW\Providers;

    use STDW\Contract\ServiceProviderAbstracted;
    use STDW\Http\Routing\Contract\ParserInterface;
    use STDW\Http\Routing\Contract\RouteCollectionInterface;
    use STDW\Http\Routing\Contract\RouteInterface;
    use STDW\Http\Routing\Parser;
    use STDW\Http\Routing\RouteCollection;
    use STDW\Http\Routing\Router;


    class RouterServiceProvider extends ServiceProviderAbstracted
    {
        public function register(): void
        {
            $this->app->singleton(ParserInterface::class, Parser::class);
            $this->app->singleton(RouteCollectionInterface::class, RouteCollection::class);
            $this->app->singleton(RouteInterface::class, Router::class);
        }

        public function boot(): void
        { }

        public function terminate(): void
        { }
    }