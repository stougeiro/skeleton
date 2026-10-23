<?php declare(strict_types=1);

namespace Sourcerer\Http;

use Sourcerer\Error\ErrorFallback;
use Sourcerer\Error\ErrorHandlerInterface;
use Sourcerer\Module\ModuleProviderInterface;
use STDW\Container\ServiceManager;
use STDW\Contract\Container\ContainerInterface;
use STDW\Contract\Container\ServiceManagerInterface;
use STDW\Contract\Container\ServiceProviderInterface;
use STDW\Contract\Http\RequestHandlerInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Http\Response;


/**
 * Application lifecycle: services register and boot first, and only then the
 * modules configure themselves over the already configured application.
 *
 * The kernel is built once per process and boots once, no matter how many
 * requests it handles.
 */
final class Kernel
{
    private static ?self $instance = null;

    private bool $booted = false;


    /**
     * @param array<int, ModuleProviderInterface> $modules
     */
    protected function __construct(
        private readonly ContainerInterface $container,
        private readonly ServiceManagerInterface $manager,
        private readonly array $modules,
    ) { }


    /**
     * @return self
     */
    public static function create(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        container()->singleton(ServiceManagerInterface::class, ServiceManager::class);

        $container = container();

        /** @var ServiceManagerInterface $manager */
        $manager = $container->get(ServiceManagerInterface::class);

        /** @var array<int, class-string<ServiceProviderInterface>> $providers */
        $providers = config('app.providers', []);

        foreach ($providers as $provider) {
            $manager->add(new $provider($container));
        }

        /** @var array<int, class-string<ModuleProviderInterface>> $modules */
        $modules = config('app.modules', []);
        $instances = [];

        foreach ($modules as $module) {
            $instances[] = new $module($container);
        }

        return self::$instance = new self($container, $manager, $instances);
    }


    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $response = new Response();

        try {
            $this->boot();

            /** @var RequestHandlerInterface $handler */
            $handler = $this->container->get(RequestHandlerInterface::class);

            return $handler->handle($request, $response);
        }
        catch (\Throwable $error) {
            try {
                /** @var ErrorHandlerInterface $errors */
                $errors = $this->container->get(ErrorHandlerInterface::class);

                return $errors->handle($error, $request, $response);
            }
            catch (\Throwable $fatal) {
                return ErrorFallback::response($fatal, $response);
            }
        }
    }

    /**
     * @return void
     */
    public function terminate(): void
    {
        $this->manager->terminate();
    }


    /**
     * @return void
     */
    private function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->manager->register();
        $this->manager->boot();

        foreach ($this->modules as $module) {
            $module->configure();
        }

        $this->booted = true;
    }
}
