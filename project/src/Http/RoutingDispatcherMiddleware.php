<?php declare(strict_types=1);

namespace Sourcerer\Http;

use Closure;
use RuntimeException;

use STDW\Contract\Container\ContainerInterface;
use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Contract\Http\Router\RouterInterface;
use STDW\Contract\View\ViewManagerInterface;
use STDW\Http\Middleware\MiddlewareManager;


/**
 * Final middleware of the application queue: it matches the route, feeds the
 * route variables into the request and runs the route chain (module and
 * controller middlewares) over the accumulated response, the controller being
 * the chain's final handler.
 */
final class RoutingDispatcherMiddleware implements MiddlewareInterface
{
    /**
     * Controllers already validated in this process: the check is made once
     * per controller, not once per request.
     *
     * @var array<string, true>
     */
    private array $validated = [];


    public function __construct(
        private readonly ContainerInterface $container,
        private readonly RoutingMiddlewareCollection $middlewares,
    ) { }


    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Closure $next
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
    {
        /** @var RouterInterface $router */
        $router = $this->container->get(RouterInterface::class);

        $route = $router->match($request);

        if (is_null($route)) {
            /** @var ViewManagerInterface $view */
            $view = $this->container->get(ViewManagerInterface::class);

            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($view->render('error:notfound'));
        }

        foreach ($route->getVariables() as $name => $value) {
            $request->withAttribute($name, $value);
        }

        /** @var class-string<ControllerInterface> $controller */
        $controller = $route->getController();

        if ( ! isset($this->validated[$controller])) {
            if ( ! is_subclass_of($controller, ControllerInterface::class)) {
                throw new RuntimeException("route: controller '{$controller}' is not a controller");
            }

            $this->validated[$controller] = true;
        }

        $manager = new MiddlewareManager();

        foreach ($this->middlewares->forController($controller) as $middleware) {
            $manager->add($middleware);
        }

        $handler = $this->container->make($controller);

        $manager->setFinalHandler($handler);

        return $manager->handle($request, $response);
    }
}
