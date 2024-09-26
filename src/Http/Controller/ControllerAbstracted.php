<?php declare(strict_types=1);

    namespace STDW\Http\Controller;

    use STDW\Http\Filter\FilterAbstracted;
    use STDW\Http\Filter\FilterException;


    abstract class ControllerAbstracted
    {
        protected static array $filters = [];

        protected static array $acceptedMethods = [
            'get', 'post', 'put', 'delete'
        ];

        protected array $route_params = [];


        protected function setParams(array $params): void
        {
            foreach ($params as $param => $value) {
                $this->route_params[$param] = $value;
            }
        }


        public function __construct()
        { }

        public function param(string $param): string
        {
            if ( ! in_array($param, array_keys($this->route_params))) {
                return '';
            }

            return $this->route_params[$param];
        }


        public static function run(array $route_params = []): void
        {
            $class = get_called_class();

            if ( ! is_a($class, ControllerAbstracted::class, true)) {
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
                if ( ! is_a($filter, FilterAbstracted::class, true)) {
                    throw FilterException::invalidFilter($filter);
                }

                (new $filter)($route_params);
            }


            $instance = new $class;
            $instance->setParams($route_params);
            $instance->$http_method();

            unset($instance);
        }


        protected static function acceptedMethods(): array
        {
            return static::$acceptedMethods;
        }

        protected static function filters(array $filters = []): array
        {
            return array_unique( array_merge(static::$filters, $filters));
        }
    }