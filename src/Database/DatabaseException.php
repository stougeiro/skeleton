<?php declare(strict_types=1);

    namespace STDW\Database;

    use Exception;


    class DatabaseException extends Exception
    {
        public static function invalidSchema(): object
        {
            return new static("Database configuration: Configuration schema is not valid.");
        }

        public static function invalidDriverConfiguration(string $driver, array $supported_drivers): object
        {
            return new static("Database configuration: Driver '". $driver ."' not supported (supported drivers: '". implode(', ', $supported_drivers) ."').");
        }

        public static function invalidQueryParameter(): object
        {
            return new static("Model: Parameter needs to be an instance of '".Param::class."' class.");
        }

        public static function unsupportedParameterType(): object
        {
            return new static("Param: Unsupported parameter type. Please inspect predefined constants for PDO.");
        }
    }