<?php declare(strict_types=1);

namespace App\HelloWorld\Middleware;

use Closure;

use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;


/**
 * Controller level middleware: it runs only for the HelloWorldController and
 * accumulates on top of what the module level already wrote.
 */
final class HelloWorldOnlyMiddleware implements MiddlewareInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Closure $next
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
    {
        $chain = $response->getHeader('X-Chain');

        $response->withHeader('X-Chain', is_null($chain) ? 'controller' : $chain . ' controller');

        return $next($request, $response);
    }
}
