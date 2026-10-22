<?php declare(strict_types=1);

namespace Sourcerer\Http;

use Closure;
use ReflectionMethod;

use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Contract\View\ViewManagerInterface;


/**
 * Base controller: it receives the response accumulated by the middlewares,
 * answers one method per HTTP verb (405 until a concrete controller overrides
 * the verbs it serves) and resolves its own services from the container.
 *
 * The controller is the final handler of the route chain, so its process()
 * simply stores the request/response pair and delegates to handle().
 *
 * @phpstan-consistent-constructor
 */
abstract class ControllerAbstracted implements ControllerInterface
{
    protected RequestInterface $request;

    protected ResponseInterface $response;


    /**
     * @return ResponseInterface
     */
    public function get(): ResponseInterface
    {
        return $this->notAllowed();
    }

    /**
     * @return ResponseInterface
     */
    public function post(): ResponseInterface
    {
        return $this->notAllowed();
    }

    /**
     * @return ResponseInterface
     */
    public function put(): ResponseInterface
    {
        return $this->notAllowed();
    }

    /**
     * @return ResponseInterface
     */
    public function patch(): ResponseInterface
    {
        return $this->notAllowed();
    }

    /**
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        return $this->notAllowed();
    }

    /**
     * @return ResponseInterface
     */
    public function options(): ResponseInterface
    {
        return $this->response
            ->withStatus(204)
            ->withBody(null)
            ->withHeader('Allow', $this->allowedMethods());
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

        return match (strtolower($this->request->getMethod())) {
            'get' => $this->get(),
            'post' => $this->post(),
            'put' => $this->put(),
            'patch' => $this->patch(),
            'delete' => $this->delete(),
            'options' => $this->options(),
            default => $this->notAllowed(),
        };
    }


    /**
     * @return ResponseInterface
     */
    protected function notAllowed(): ResponseInterface
    {
        return $this->view('error:notallowed')
            ->withStatus(405)
            ->withHeader('Allow', $this->allowedMethods());
    }

    /**
     * The verbs the concrete controller overrides, in declaration order.
     *
     * @return string
     */
    protected function allowedMethods(): string
    {
        $allowed = [];

        foreach (['get', 'post', 'put', 'patch', 'delete', 'options'] as $verb) {
            $declaring = (new ReflectionMethod($this, $verb))->getDeclaringClass()->getName();

            if ($declaring !== self::class) {
                $allowed[] = strtoupper($verb);
            }
        }

        return implode(', ', $allowed);
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
