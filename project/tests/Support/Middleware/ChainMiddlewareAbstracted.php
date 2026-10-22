<?php declare(strict_types=1);

namespace Tests\Support\Middleware;

use Closure;

use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;


abstract class ChainMiddlewareAbstracted implements MiddlewareInterface
{
    /**
     * @return string
     */
    abstract protected function token(): string;


    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Closure $next
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
    {
        $chain = $response->getHeader('X-Chain');

        $response->withHeader('X-Chain', is_null($chain) ? $this->token() : $chain . ' ' . $this->token());

        return $next($request, $response);
    }
}
