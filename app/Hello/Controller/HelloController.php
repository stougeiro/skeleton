<?php declare(strict_types=1);

    namespace App\Hello\Controller;

    use App\Hello\Contract\ModuleControllerAbstracted;


    class HelloController extends ModuleControllerAbstracted
    {
        public function get()
        {
            view()->render('hello:helloworld');
        }
    }