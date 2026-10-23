<?php declare(strict_types=1);

namespace Sourcerer\Error;

use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;


/**
 * Single entry point for application errors: it receives the throwable and
 * the response accumulated so far, so headers and cookies already set by the
 * middlewares survive the failure.
 */
interface ErrorHandlerInterface
{
    public function handle(\Throwable $error, RequestInterface $request, ResponseInterface $response): ResponseInterface;
}
