<?php declare(strict_types=1);

use STDW\Http\Request;

use Sourcerer\Error\ErrorHandler;
use Sourcerer\Http\Kernel;


try
{
    require_once __DIR__ . '/../bootstrap/app.php';


    ErrorHandler::register();

    $kernel = Kernel::create();

    $kernel->handle(new Request())->send();
    $kernel->terminate();
}

catch (Throwable $error)
{
    error_log((string) $error);

    if (headers_sent()) {
        return;
    }

    header_remove();
    http_response_code(500);
}
