<?php declare(strict_types=1);

namespace Sourcerer\Http;

use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\ResponseInterface;


/**
 * A controller answers the HTTP verbs it exposes as public access methods
 * ('get', 'post', 'purge', ...) — the framework always answers 'options'.
 * Helpers must be protected or private, otherwise they become verbs.
 */
interface ControllerInterface extends MiddlewareInterface
{
    public function options(): ResponseInterface;
}
