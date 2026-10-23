<?php declare(strict_types=1);

namespace Sourcerer\Error\Exception;

use Throwable;


/**
 * Base for HTTP aware exceptions: carries the status and the headers the
 * response must send, keeping the Throwable surface of RuntimeException.
 */
abstract class HttpExceptionAbstracted extends \RuntimeException implements HttpExceptionInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly int $status,
        string $message = '',
        private readonly array $headers = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
