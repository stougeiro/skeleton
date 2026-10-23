<?php declare(strict_types=1);

namespace Sourcerer\Http;

use Closure;

use Sourcerer\Error\Exception\MethodNotAllowedHttpException;

use STDW\Contract\Container\ContainerInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Contract\View\ViewManagerInterface;


/**
 * Base controller: verbs are derived from public methods of the concrete
 * controller (own + inherited from app bases), minus methods declared on this
 * base class, unioned with 'options'. Helpers must be protected or private,
 * otherwise they become verbs. The constructor is left free for the concrete
 * controller to use with ServiceFactoryInterface and container::make().
 */
abstract class ControllerAbstracted implements ControllerInterface
{
    protected RequestInterface $request;

    protected ResponseInterface $response;


    /**
     * @return ResponseInterface
     */
    public function options(): ResponseInterface
    {
        return $this->response
            ->withStatus(204)
            ->withBody('')
            ->withHeader('Allow', $this->allowHeader());
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response The response accumulated by the middlewares.
     * @param Closure $next
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;

        $method = strtolower($this->request->getMethod());

        if ( ! in_array($method, $this->allowedMethods(), true)) {
            return $this->notAllowed();
        }

        /** @var callable(): ResponseInterface $handler */
        $handler = [$this, $method];

        return $handler();
    }


    /**
     * Public methods of the concrete controller (own + inherited from app bases)
     * minus methods declared on this base class, plus 'options'.
     *
     * @return list<string>
     */
    protected function allowedMethods(): array
    {
        $own = array_diff(
            array_map('strtolower', get_class_methods(static::class)),
            array_map('strtolower', get_class_methods(self::class)),
        );

        return array_values(array_unique([...array_values($own), 'options']));
    }

    /**
     * @return ResponseInterface
     */
    protected function notAllowed(): ResponseInterface
    {
        throw new MethodNotAllowedHttpException($this->allowHeader());
    }

    /**
     * @return string
     */
    protected function allowHeader(): string
    {
        return implode(', ', array_map('strtoupper', $this->allowedMethods()));
    }

    /**
     * Renders a view through the view manager.
     *
     * @param string $template
     * @param array<string, mixed> $data
     * @return ResponseInterface
     */
    protected function view(string $template, array $data = []): ResponseInterface
    {
        /** @var ViewManagerInterface $view */
        $view = container()->get(ViewManagerInterface::class);

        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($view->render($template, $data));
    }
}
