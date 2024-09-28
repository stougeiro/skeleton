<?php declare(strict_types=1);

    use STDW\Http\Routing\Contract\RouteInterface;
    use STDW\Database\Database;


    if ( ! function_exists('router'))
    {
        function router(): RouteInterface
        {
            return app()->make(RouteInterface::class);
        }
    }

    if ( ! function_exists('database'))
    {
        function database(string $connection = 'default'): object
        {
            if ( ! in_array($connection, Database::$instances)) {
                return new Database($connection);
            }

            return Database::$instances[$connection];
        }
    }