<?php declare(strict_types=1);

    namespace STDW\Providers;

    use STDW\Contract\ServiceProviderAbstracted;


    class ModuleServiceProvider extends ServiceProviderAbstracted
    {
        protected array $modules;


        public function register(): void
        {
            $this->modules = config('modules') ?? [];

            foreach ($this->modules as $module) {
                $this->app->singleton($module);
            }
        }

        public function boot(): void
        {
            foreach ($this->modules as $module) {
                $this->app->make($module)->configure();
            }
        }

        public function terminate(): void
        { }
    }