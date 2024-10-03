<?php declare(strict_types=1);

    namespace STDW\Http\Controller;

    use STDW\Http\Filter\FilterAbstracted;
    use STDW\Http\Filter\FilterException;


    abstract class ControllerAbstracted
    {
        protected static array $filters = [];

        protected static array $parameters = [];

        protected static array $acceptedMethods = [
            'get', 'post', 'put', 'delete'
        ];


        protected static function acceptedMethods(): array
        {
            return static::$acceptedMethods;
        }

        protected static function filters(array $filters = []): array
        {
            return array_unique( array_merge(static::$filters, $filters));
        }

        protected static function setParameters(array $parameters): void
        {
            static::$parameters = array_merge(static::$parameters, $parameters);
        }


        public function __construct()
        { }


        protected function parameter(string $parameter): ?string
        {
            return static::$parameters[$parameter] ?? null;
        }


        public static function run(array $parameters = []): void
        {
            $class = get_called_class();

            if ( ! is_subclass_of($class, ControllerAbstracted::class)) {
                throw ControllerException::invalidController($class);
            }


			$http_method = request_method();

            if ( ! in_array($http_method, $class::acceptedMethods())) {
                throw ControllerException::httpMethodNotAllowed($http_method);
            }

            if ( ! in_array($http_method, get_class_methods($class))) {
                throw ControllerException::httpMethodNotFound($http_method);
            }


            $filters = array_unique($class::filters());

            foreach ($filters as $filter) {
                if ( ! is_subclass_of($filter, FilterAbstracted::class)) {
                    throw FilterException::invalidFilter($filter);
                }

                (app()->make($filter))($parameters);
            }


            $class::setParameters($parameters);

            (app()->make($class))->$http_method();
        }
    }