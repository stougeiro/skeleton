<?php declare(strict_types=1);

namespace Sourcerer\Error;

use STDW\Contract\Http\RequestInterface;


/**
 * Destination of every handled error: the id links what the user saw to the
 * stored record, and the full context stays out of the response.
 */
interface ErrorReporterInterface
{
    public function report(string $id, \Throwable $error, RequestInterface $request, int $status): void;
}
