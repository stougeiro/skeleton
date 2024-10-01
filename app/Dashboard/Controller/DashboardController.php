<?php declare(strict_types=1);

    namespace App\Dashboard\Controller;

    use App\Dashboard\Contract\ModuleControllerAbstracted;


    class DashboardController extends ModuleControllerAbstracted
    {
        public function get()
        {
            view()->render('dashboard:helloworld');
        }
    }