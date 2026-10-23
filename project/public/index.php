<?php declare(strict_types=1);

use Sourcerer\Error\ErrorFallback;
use Sourcerer\Http\Kernel;
use STDW\Http\Request;

$start = hrtime(true);

try {
    require_once __DIR__ . '/../bootstrap/app.php';

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
catch (\Throwable $error) {
    if (class_exists(ErrorFallback::class)) {
        ErrorFallback::send($error);

        return;
    }

    error_log((string) $error);

    if ( ! headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<h1>Erro interno</h1>';
}
