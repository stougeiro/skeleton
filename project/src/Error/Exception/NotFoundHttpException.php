<?php declare(strict_types=1);

namespace Sourcerer\Error\Exception;


/**
 * No route matched the request: the answer is a 404 rendered from the
 * error module.
 */
final class NotFoundHttpException extends HttpExceptionAbstracted
{
    public function __construct(string $message = 'Not Found', ?\Throwable $previous = null)
    {
        parent::__construct(404, $message, [], $previous);
    }
}
