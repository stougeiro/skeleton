<?php declare(strict_types=1);

    namespace STDW\App;

    use STDW\Contract\AppAbstracted;
    use STDW\Container\Contract\ContainerInterface;
    use STDW\Container\Contract\ContainerUtilsTrait;
    use Exception;


    class App extends AppAbstracted
    {
        use ContainerUtilsTrait;


        protected ContainerInterface $container;

        protected array $providers = [];

        protected array $modules = [];


        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;

            $this->providers = config('providers') ?? [];

            foreach ($this->providers as $provider) {
                $this->singleton($provider);
            }

            $this->modules = config('modules') ?? [];

            foreach ($this->modules as $module) {
                $this->singleton($module);
            }

            static::$instance = $this;
        }


        public function register(): void
        {
            foreach ($this->providers as $provider) {
                $this->make($provider)->register();
            }

            foreach ($this->modules as $module) {
                $this->make($module)->register();
            }
        }

        public function boot(): void
        {
            foreach ($this->providers as $provider) {
                $this->make($provider)->boot();
            }
        }

        public function configure(): void
        {
            foreach ($this->modules as $module) {
                $this->make($module)->configure();
            }
        }

        public function terminate(): void
        {
            foreach ($this->providers as $provider) {
                $this->make($provider)->terminate();
            }
        }


        public function run(): void
        {
            try
            {
                $this->register();
                $this->boot();
                $this->configure();
                
                router()->listen();
                
                $this->terminate();
            }
            catch(Exception $e)
            {
                debug($e);
            }
        }
    }