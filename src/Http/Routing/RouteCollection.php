<?php declare(strict_types=1);

    namespace STDW\Http\Routing;

    use STDW\Http\Routing\Contract\RouteCollectionInterface;
    use STDW\Http\Routing\Contract\ParserInterface;
    use STDW\Http\Routing\Exception\RouteException;
    use STDW\Cache\Contract\CacheInterface;
    use Throwable;


    class RouteCollection implements RouteCollectionInterface
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