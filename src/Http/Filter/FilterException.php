<?php declare(strict_types=1);

    namespace STDW\Http\Filter;

    use Exception;


    class FilterException extends Exception
    {
        public static function invalidFilter(string $filter): object
        {
            return new static('Filter "'. $filter .'" is not valid');
        }
    }