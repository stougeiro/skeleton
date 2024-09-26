<?php declare(strict_types=1);

    namespace STDW\Http\Routing;

    use STDW\Http\Routing\Contract\RouteInterface;


    class Router implements RouteInterface
    {
        public function __construct(
            protected RouteCollection $collection)
        { }


        public function getRoutes(): array
        {
            return $this->collection->getRoutes();
        }

        public function setRoutes(array $routes): void
        {
            $this->collection->setRoutes($routes);
        }

        public function map(array $routemaps, string $prefix = ''): void
        {
            $this->collection->map($routemaps, $prefix);
        }

        public function listen(): void
        {
            $uri = Uri::get();
            $route = $this->collection->match($uri);

            call_user_func_array([$route['controller'], 'run'], [$route['variables']]);
        }
    }