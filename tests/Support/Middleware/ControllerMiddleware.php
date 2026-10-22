<?php declare(strict_types=1);

namespace Tests\Support\Middleware;


final class ControllerMiddleware extends ChainMiddlewareAbstracted
{
    /**
     * @return string
     */
    protected function token(): string
    {
        return 'controller';
    }
}
