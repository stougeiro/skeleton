<?php declare(strict_types=1);

    namespace STDW\Http\Controller;

    use Exception;


    class ControllerException extends Exception
    {
        public static function invalidController(string $controller): object
        {
            return new static('Controller "'. $controller .'" is not valid');
        }

        public static function httpMethodNotAllowed(string $method): object
        {
            return new static('Method "'. $method .'" is not allowed');
        }

        public static function httpMethodNotFound(string $method): object
        {
            return new static('Method "'. $method .'" not found');
        }
    }