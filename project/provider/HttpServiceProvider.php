<?php declare(strict_types=1);

namespace App\Provider;

use STDW\Contract\Container\ServiceProviderAbstracted;
use STDW\Contract\Container\ContainerInterface;
use STDW\Contract\Http\MiddlewareManagerInterface;
use STDW\Contract\Http\Router\RouteCollectionInterface;
use STDW\Contract\Http\Router\RouterInterface;
use STDW\Contract\Http\RequestHandlerInterface;
use STDW\Contract\Cache\CacheInterface;
use STDW\Http\Middleware\MiddlewareManager;
use STDW\Http\Router\RouteCollection;
use STDW\Http\Router\Router;
use STDW\Http\Handler\RequestHandler;

use Sourcerer\Http\RoutingMiddlewareCollection;


/**
 * HTTP plumbing: the middleware manager, the route collection, the router and
 * the request handler. The router is lazy, so it only snapshots the route
 * collection at the dispatch, after every module had the chance to load its
 * routes.
 */
final class HttpServiceProvider extends ServiceProviderAbstracted
{
    public function register(): void
    {
        $this->container->singleton(MiddlewareManagerInterface::class, MiddlewareManager::class);

        $this->container->singleton(RouteCollectionInterface::class, static function (ContainerInterface $container): RouteCollectionInterface {
            /** @var CacheInterface $cache */
            $cache = $container->get(CacheInterface::class);

            return new RouteCollection($cache);
        });

        $this->container->singleton(RouterInterface::class, static function (ContainerInterface $container): RouterInterface {
            /** @var RouteCollectionInterface $collection */
            $collection = $container->get(RouteCollectionInterface::class);

            /** @var CacheInterface $cache */
            $cache = $container->get(CacheInterface::class);

            /** @var array{cache: bool} $config */
            $config = config('router');

            return new Router($collection, $cache, $config['cache']);
        });

        $this->container->singleton(RequestHandlerInterface::class, static function (ContainerInterface $container): RequestHandlerInterface {
            /** @var MiddlewareManagerInterface $manager */
            $manager = $container->get(MiddlewareManagerInterface::class);

            return new RequestHandler($manager);
        });
    }

    public function boot(): void
    {
        /** @var RoutingMiddlewareCollection $middlewares */
        $middlewares = $this->container->get(RoutingMiddlewareCollection::class);

        /** @var MiddlewareManagerInterface $manager */
        $manager = $this->container->get(MiddlewareManagerInterface::class);

        foreach ($middlewares->globals() as $middleware) {
            $manager->add($middleware);
        }
    }
}
