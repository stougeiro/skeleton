<?php declare(strict_types=1);

namespace App\HelloWorld\Controller;

use Sourcerer\Http\ControllerAbstracted;
use STDW\Contract\Http\ResponseInterface;


/**
 * Renders the module view over the response accumulated by the middlewares.
 */
final class HelloWorldController extends ControllerAbstracted
{
    /**
     * @return ResponseInterface
     */
    public function get(): ResponseInterface
    {
        return $this->view('hello:helloworld');
    }
}
