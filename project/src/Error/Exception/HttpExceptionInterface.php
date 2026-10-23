<?php declare(strict_types=1);

namespace Sourcerer\Error\Exception;

use Throwable;


/**
 * An error that already knows the HTTP answer: the status to send and the
 * headers that must travel with it (e.g. Allow on a 405).
 */
interface HttpExceptionInterface extends Throwable
{
    /**
     * @return int
     */
    public function status(): int;

    /**
     * @return array<string, string>
     */
    public function headers(): array;
}
