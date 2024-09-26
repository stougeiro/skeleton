<?php declare(strict_types=1);

    namespace STDW\Http\Routing;

    use STDW\Http\Routing\Contract\RouteCollectionInterface;
    use STDW\Http\Routing\Contract\ParserInterface;
    use STDW\Http\Routing\Exception\RouteException;
    use STDW\Http\Routing\Exception\RouteNotFoundException;
    use STDW\Cache\Contract\CacheInterface;
    use Throwable;


    class RouteCachedCollection implements RouteCollectionInterface
    {
        private array $routes = [];

        private string $basePath = '/';

        private bool $caching = false;


        public function __construct(
            protected ParserInterface $parser,
            protected CacheInterface $cache)
        {
            try {
                $this->basePath = config('routing.base_path');
            } catch (Throwable $e) { }

            try {
                $this->caching = config('routing.caching');
            } catch (Throwable $e) { }
        }


        public function getRoutes(): array
        {
            return $this->routes;
        }

        public function setRoutes(array $routes): void
        {
            $this->routes = $routes;
        }

        public function map(array $routemaps, string $prefix = ''): void
        {
            if ($this->caching && $this->cache->has('routes')) {
                return;
            }

            foreach ($routemaps as $route => $mix) {
                if (is_string($mix)) {
                    $this->add($prefix.'/'.$route, $mix);
                } elseif (is_array($mix)) {
                    $this->map($mix, $prefix.'/'.$route);
                } else {
                    throw RouteException::invalidRouteMap($mix);
                }
            }
        }

        public function match(array $uri): array
        {
            if ($this->caching && ! $this->cache->has('routes')) {
                $this->cache->set('routes', $this->routes);
            }

            $collection = ($this->caching && $this->cache->has('routes')) ? $this->cache->get('routes') : $this->routes;

            if ( ! isset($collection[$uri['parts']])) {
                goto notfound;
            }


            $match = false;
            $routes = array_values($collection[$uri['parts']]);

            foreach ($routes as $route) {
                if (preg_match($route['route'], $uri['uri'], $variables)) {
                    $match = true; break;
                }
            }

            if ($match) {
                return [
                    'uri' => $uri['uri'],
                    'parts' => $uri['parts'],
                    'map' => $route['map'],
                    'route' => $route['route'],
                    'controller' => $route['controller'],
                    'variables' => array_filter($variables, 'is_string', ARRAY_FILTER_USE_KEY),
                ];
            }


            notfound:

            throw new RouteNotFoundException($uri['uri']);
        }


        protected function add(string $uri, string $controller): void
        {
            $uri = $this->basePath .'/'. $uri .'/';
            $route = $this->parser->parse($uri);

            $this->routes[$route['parts']][$route['routemap']] = [
                'map' => $route['routemap'],
                'route' => $route['route'],
                'controller' => $controller,
            ];
        }
    }