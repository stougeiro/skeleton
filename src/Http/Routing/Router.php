<?php declare(strict_types=1);

    namespace STDW\Http\Routing;

    use STDW\Http\Routing\Contract\RouteCollectionInterface;
    use STDW\Http\Routing\Contract\RouterInterface;
    use STDW\Http\Routing\Exception\RouteNotFoundException;
    use STDW\Cache\Contract\CacheInterface;
    use Throwable;


    class Router implements RouterInterface
    {
        private bool $caching = false;


        public function __construct(
            protected RouteCollectionInterface $collection,
            protected CacheInterface $cache)
        {
            try {
                $this->caching = config('routing.caching');
            } catch (Throwable $e) { }
        }


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
            $route = $this->match($uri);

            call_user_func_array([$route['controller'], 'run'], [$route['variables']]);
        }


        protected function match(Uri $uri): array
        {
            if ($this->caching && ! $this->cache->has('routes')) {
                $this->cache->set('routes', $this->getRoutes());
            }

            $collection = ($this->caching && $this->cache->has('routes')) ? $this->cache->get('routes') : $this->getRoutes();

            if ( ! isset($collection[$uri->parts])) {
                goto notfound;
            }


            $routes = array_values($collection[$uri->parts]);

            foreach ($routes as $route) {
                if (preg_match($route['route'], $uri->uri, $variables)) {
                    return [
                        'uri' => $uri->uri,
                        'parts' => $uri->parts,
                        'map' => $route['map'],
                        'route' => $route['route'],
                        'controller' => $route['controller'],
                        'variables' => array_filter($variables, 'is_string', ARRAY_FILTER_USE_KEY),
                    ];
                }
            }


            notfound:

            throw new RouteNotFoundException($uri->uri);
        }
    }