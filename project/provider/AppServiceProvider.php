<?php declare(strict_types=1);

namespace App\Provider;

use STDW\Contract\Container\ContainerInterface;
use STDW\Contract\Container\ServiceProviderAbstracted;
use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\MiddlewareManagerInterface;

use Sourcerer\Http\RoutingDispatcherMiddleware;
use Sourcerer\Http\RoutingMiddlewareCollection;


/**
 * Application composition: the middleware registry (globals plus the module
 * and controller maps) and the dispatcher that closes the application queue.
 */
final class AppServiceProvider extends ServiceProviderAbstracted
{
    public function register(): void
    {
        $this->container->singleton(RoutingMiddlewareCollection::class, static function (): RoutingMiddlewareCollection {
            /** @var array<int, class-string<MiddlewareInterface>> $globals */
            $globals = config('middlewares', []);

            return new RoutingMiddlewareCollection($globals);
        });

        $this->container->singleton(RoutingDispatcherMiddleware::class, static function (ContainerInterface $container): RoutingDispatcherMiddleware {
            /** @var RoutingMiddlewareCollection $middlewares */
            $middlewares = $container->get(RoutingMiddlewareCollection::class);

            return new RoutingDispatcherMiddleware($container, $middlewares);
        });
    }

    public function boot(): void
    {
        /** @var MiddlewareManagerInterface $manager */
        $manager = $this->container->get(MiddlewareManagerInterface::class);

        /** @var RoutingDispatcherMiddleware $dispatcher */
        $dispatcher = $this->container->get(RoutingDispatcherMiddleware::class);

        $manager->setFinalHandler($dispatcher);
    }
}
