<?php declare(strict_types=1);

use STDW\Http\Request;
use STDW\Http\Response;

use Sourcerer\Error\ErrorHandler;
use Sourcerer\Http\Kernel;


$start = hrtime(true);

try
{
    require_once __DIR__ . '/../bootstrap/app.php';

    ErrorHandler::register();

    $kernel = Kernel::create();

    $kernel->handle(new Request())->send();
    $kernel->terminate();

    if (env('PERF_LOG')) {
        file_put_contents(
            LOG . 'perf.log',
            sprintf("duration: %.2f ms\n", (hrtime(true) - $start) / 1e6),
            FILE_APPEND | LOCK_EX
        );
    }
}

catch (Throwable $error)
{
    if (class_exists(ErrorHandler::class)) {
        $response = ErrorHandler::fallback($error, new Response());

        if ( ! headers_sent()) {
            $response->send();
        }

        return;
    }

    error_log((string) $error);

    if (headers_sent()) {
        return;
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');

    echo '<h1>Erro interno</h1>';
}
