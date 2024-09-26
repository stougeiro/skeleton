<?php declare(strict_types=1);

    namespace STDW\Http\Routing\Contract;


    interface RouteCollectionInterface
    {
        public function getRoutes(): array;

        public function setRoutes(array $routes): void;

        public function map(array $routemaps, string $prefix = ''): void;
    }