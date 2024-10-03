<?php declare(strict_types=1);

    namespace App\Hello;

    use STDW\Contract\ModuleProviderAbstracted;

    use App\Hello\Controller\HelloController;


    class HelloModuleProvider extends ModuleProviderAbstracted
    {
        protected array $routes =
        [
            '/' => HelloController::class,
        ];


        public function register(): void
        { }

        public function configure(): void
        {
            router()->map($this->routes);

            view()->setStorage('hello', __DIR__.'/resources/view');
        }
    }