<?php declare(strict_types=1);

namespace Sourcerer\Error\Exception;


/**
 * The route exists but the verb is not accepted: the answer is a 405 that
 * keeps the Allow header describing what the controller accepts.
 */
final class MethodNotAllowedHttpException extends HttpExceptionAbstracted
{
    public function __construct(string $allow, ?\Throwable $previous = null)
    {
        parent::__construct(405, 'Method Not Allowed', ['Allow' => $allow], $previous);
    }
}
