<?php declare(strict_types=1);

namespace Sourcerer\Http;

use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\ResponseInterface;


interface ControllerInterface extends MiddlewareInterface
{
    public function get(): ResponseInterface;

    public function post(): ResponseInterface;

    public function put(): ResponseInterface;

    public function patch(): ResponseInterface;

    public function delete(): ResponseInterface;

    public function options(): ResponseInterface;
}
