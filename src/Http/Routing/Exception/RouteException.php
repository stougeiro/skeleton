<?php declare(strict_types=1);

    namespace STDW\Http\Routing\Exception;

    use Exception;


    class RouteException extends Exception
    {
        public static function invalidRouteMap(string $map): object
        {
            return new static('Route "'. $map .'" is not valid. The route cannot be mapped.');
        }
    }