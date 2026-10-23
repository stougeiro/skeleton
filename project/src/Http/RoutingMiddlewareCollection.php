<?php declare(strict_types=1);

namespace Sourcerer\Http;

use STDW\Contract\Http\MiddlewareInterface;

use Sourcerer\Module\ModuleProviderInterface;

use RuntimeException;


/**
 * Middlewares of the application, by scope: the globals
 * (config/middlewares.php), the module level ('*') and the controller level
 * (the exact controller key) declared by each module in its
 * Config/middlewares.php.
 */
final class RoutingMiddlewareCollection
{
    /**
     * Module namespace => the module's middleware map.
     *
     * @var array<string, array<string, array<int, class-string<MiddlewareInterface>>>>
     */
    private array $modules = [];

    /**
     * Controller FQCN => its middlewares, already instantiated: the resolution
     * of a controller is deterministic, so it is made only once per process.
     *
     * @var array<string, array<int, MiddlewareInterface>>
     */
    private array $resolved = [];

    /**
     * @var array<int, MiddlewareInterface>|null
     */
    private ?array $resolvedGlobals = null;


    /**
     * @param array<int, class-string<MiddlewareInterface>> $globals
     */
    public function __construct(
        private readonly array $globals = [],
    ) { }


    /**
     * Registers the middleware map of a module.
     *
     * The map keys are '*' (the whole module) or a controller FQCN (a single
     * controller); the values are lists of middleware classes.
     *
     * @param class-string<ModuleProviderInterface> $module The module provider class.
     * @param string $file The file returning the map.
     * @return void
     */
    public function load(string $module, string $file): void
    {
        if ( ! is_subclass_of($module, ModuleProviderInterface::class)) {
            throw new RuntimeException("middlewares: module '{$module}' is not a module provider");
        }

        $path = realpath($file);

        if ($path === false) {
            throw new RuntimeException("middlewares: file '{$file}' not found");
        }

        $map = include $path;

        if ( ! is_array($map)) {
            throw new RuntimeException("middlewares: file '{$file}' must return an array");
        }

        $normalized = [];

        foreach ($map as $key => $middlewares) {
            if ( ! is_string($key)) {
                throw new RuntimeException("middlewares: invalid key '{$key}', expected '*' or a controller FQCN");
            }

            if ( $key !== '*' && ! is_subclass_of($key, ControllerInterface::class)) {
                throw new RuntimeException("middlewares: invalid key '{$key}', expected '*' or a controller FQCN");
            }

            if ( ! is_array($middlewares)) {
                throw new RuntimeException("middlewares: the value of '{$key}' must be an array");
            }

            $list = [];

            foreach ($middlewares as $middleware) {
                if ( ! is_string($middleware) || ! is_subclass_of($middleware, MiddlewareInterface::class)) {
                    throw new RuntimeException("middlewares: the list of '{$key}' must hold middleware class names");
                }

                /** @var class-string<MiddlewareInterface> $middleware */
                $list[] = $middleware;
            }

            $normalized[$key] = $list;
        }

        $this->modules[$this->namespaceOf($module)] = $normalized;

        /** The maps changed: the resolution cache is no longer valid. */
        $this->resolved = [];
    }


    /**
     * The application level middlewares, ready to run.
     *
     * @return array<int, MiddlewareInterface>
     */
    public function globals(): array
    {
        return $this->resolvedGlobals ??= $this->resolve($this->globals);
    }


    /**
     * The module and controller level middlewares of a controller, ready to
     * run: the module ones ('*') come before the controller ones.
     *
     * @param string $controller
     * @return array<int, MiddlewareInterface>
     */
    public function forController(string $controller): array
    {
        if ( isset($this->resolved[$controller])) {
            return $this->resolved[$controller];
        }

        $map = $this->mapOf($controller);

        $moduleLevel = $map['*'] ?? [];
        $controllerLevel = $map[$controller] ?? [];

        return $this->resolved[$controller] = $this->resolve([...$moduleLevel, ...$controllerLevel]);
    }


    /**
     * The map of the module that owns the controller; the longest namespace
     * wins when more than one matches.
     *
     * @param string $controller
     * @return array<string, array<int, class-string<MiddlewareInterface>>>
     */
    private function mapOf(string $controller): array
    {
        $map = [];
        $length = 0;

        foreach ($this->modules as $namespace => $candidate) {
            if (str_starts_with($controller, $namespace) && strlen($namespace) > $length) {
                $map = $candidate;
                $length = strlen($namespace);
            }
        }

        return $map;
    }


    /**
     * @param class-string $module
     * @return string
     */
    private function namespaceOf(string $module): string
    {
        $position = strrpos($module, '\\');

        if ($position === false) {
            throw new RuntimeException("middlewares: module '{$module}' has no namespace");
        }

        return substr($module, 0, $position + 1);
    }


    /**
     * @param array<int, class-string<MiddlewareInterface>> $classes
     * @return array<int, MiddlewareInterface>
     */
    private function resolve(array $classes): array
    {
        $middlewares = [];

        foreach (array_unique($classes) as $class) {
            $middlewares[] = new $class();
        }

        return $middlewares;
    }
}
