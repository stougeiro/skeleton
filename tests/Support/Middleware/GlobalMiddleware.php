<?php declare(strict_types=1);

namespace Tests\Support\Middleware;


final class GlobalMiddleware extends ChainMiddlewareAbstracted
{
    /**
     * @return string
     */
    protected function token(): string
    {
        return 'global';
    }
}
