<?php declare(strict_types=1);

namespace Sourcerer\Error;

use STDW\Contract\Http\ResponseInterface;


/**
 * Last resort when the container or the handler itself is unavailable: no
 * dependencies, no views, no config, so it works even when the application
 * did not boot. It only logs and answers a plain 500.
 */
final class ErrorFallback
{
    public static function response(\Throwable $error, ResponseInterface $response): ResponseInterface
    {
        $id = self::log($error);

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('X-Error-Id', $id)
            ->withBody(self::html($id));
    }

    public static function send(\Throwable $error): void
    {
        $id = self::log($error);

        if (headers_sent()) {
            return;
        }

        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Error-Id: ' . $id);

        echo self::html($id);
    }

    private static function log(\Throwable $error): string
    {
        $id = strtoupper(bin2hex(random_bytes(8)));

        error_log(sprintf(
            '[%s] %s: %s in %s:%d',
            $id,
            $error::class,
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
        ));

        return $id;
    }

    private static function html(string $id): string
    {
        $id = htmlspecialchars($id, \ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>Erro interno</title></head><body>'
            . '<h1>Erro interno</h1>'
            . '<p>Ocorreu um erro inesperado. Código: ' . $id . '</p>'
            . '</body></html>';
    }
}
