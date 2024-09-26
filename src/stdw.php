<?php declare(strict_types=1);

    use STDW\Http\Routing\Router;
    use STDW\Database\Database;


    if ( ! function_exists('router'))
    {
        function router(): Router
        {
            return app()->make(Router::class);
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