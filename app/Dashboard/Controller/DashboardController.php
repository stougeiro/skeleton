<?php declare(strict_types=1);

    namespace App\Dashboard\Controller;

    use STDW\Http\Controller\ControllerAbstracted;


    class DashboardController extends ControllerAbstracted
    {
        protected static function filters(array $filters = []): array
        {
            return parent::filters( array_merge([
            ], $filters));
        }


        public function __construct()
        {
            parent::__construct();
        }


        public function get()
        {
            echo "Hello World";
        }
    }