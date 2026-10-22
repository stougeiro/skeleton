<?php declare(strict_types=1);

namespace Tests\Support\Middleware;

use Closure;

use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Http\Response\TextResponse;


final class EarlyMiddleware implements MiddlewareInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Closure $next
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
    {
        return new TextResponse(403, ['X-Early' => '1'], 'Blocked');
    }
}
