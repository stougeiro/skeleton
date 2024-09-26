<?php declare(strict_types=1);

    namespace App\Dashboard;

    use STDW\Contract\ModuleProviderAbstracted;

    use App\Dashboard\Controller\DashboardController;


    class DashboardModuleProvider extends ModuleProviderAbstracted
    {
        protected array $routes =
        [
            '/' => DashboardController::class,
        ];


        public function configure(): void
        {
            router()->map($this->routes);
        }
    }