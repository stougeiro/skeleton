<?php declare(strict_types=1);

    use STDW\Http\Routing\Contract\RouterInterface;
    use STDW\Database\Database;


    if ( ! function_exists('router'))
    {
        function router(): RouterInterface
        {
            return app()->make(RouterInterface::class);
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