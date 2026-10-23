<?php declare(strict_types=1);

namespace Sourcerer\Error;

use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Contract\View\ViewManagerInterface;

use Sourcerer\Error\Exception\HttpException;

use Throwable;
use ErrorException;


/**
 * Turns any throwable into an HTTP response while keeping the response that
 * the pipeline had already built. It has no injected collaborator on purpose:
 * the error path must work even with a half booted application, so config and
 * view manager are resolved when needed and every failure ends in fallback().
 */
final class ErrorHandler
{
    /**
     * Title and text of each status the application knows.
     *
     * @var array<int, array{title: string, message: string}>
     */
    private const MESSAGES = [
        400 => ['title' => 'Requisição inválida', 'message' => 'A requisição não pôde ser entendida.'],
        403 => ['title' => 'Acesso negado', 'message' => 'Você não tem permissão para acessar este recurso.'],
        404 => ['title' => 'Não encontrado', 'message' => 'O recurso solicitado não existe.'],
        405 => ['title' => 'Método não permitido', 'message' => 'O método usado não é aceito por este recurso.'],
        429 => ['title' => 'Muitas requisições', 'message' => 'Aguarde alguns instantes e tente novamente.'],
        500 => ['title' => 'Erro na aplicação', 'message' => 'Ocorreu um erro inesperado.'],
    ];


    /**
     * Installs the PHP error handler outside production, so warnings, notices
     * and deprecations become ErrorException and flow through handle() like
     * any other throwable. Call it after bootstrap, before Kernel::create().
     */
    public static function register(): void
    {
        if ( ! self::development()) {
            return;
        }

        error_reporting(E_ALL);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if ( ! (error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }


    public function handle(Throwable $error, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = self::id();
        $http = $error instanceof HttpException ? $error : null;
        $status = $http === null ? 500 : $http->status();
        $development = self::development();
        $json = $this->wantsJson($request);

        try {
            $this->log($id, $error, $request, $status);
        }
        catch (Throwable $failure) {
            error_log('error handler: unable to log the error: ' . $failure->getMessage());
        }

        $response = $response
            ->withStatus($status)
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Content-Type', $json ? 'application/json; charset=utf-8' : 'text/html; charset=utf-8');

        if ($status >= 500) {
            $response = $response->withHeader('X-Error-Id', $id);
        }

        if ($http !== null) {
            foreach ($http->headers() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }

        try {
            $body = $json
                ? $this->json($id, $error, $status, $development)
                : $this->html($id, $error, $status, $development);

            return $response->withBody($body);
        }
        catch (Throwable) {
            return self::fallback($error, $response);
        }
    }

    /**
     * Last resort without config, container or views: a plain 500 that never
     * depends on the application having booted.
     */
    public static function fallback(Throwable $error, ResponseInterface $response): ResponseInterface
    {
        $id = self::id();

        error_log(sprintf(
            '[%s] %s: %s in %s:%d',
            $id,
            $error::class,
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
        ));

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('X-Error-Id', $id)
            ->withBody(self::plain($id));
    }


    private function html(string $id, Throwable $error, int $status, bool $development): string
    {
        /** @var ViewManagerInterface $views */
        $views = container()->get(ViewManagerInterface::class);

        if ($status >= 500 && $development) {
            return $views->render('error:development', [
                'id' => $id,
                'type' => $error::class,
                'message' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'trace' => $error->getTraceAsString(),
                'snippet' => $this->snippet($error->getFile(), $error->getLine()),
            ]);
        }

        $text = self::MESSAGES[$status] ?? ['title' => 'Erro', 'message' => 'Ocorreu um erro inesperado.'];

        return $views->render('error:page', [
            'id' => $id,
            'title' => $text['title'],
            'message' => $text['message'],
        ]);
    }

    private function json(string $id, Throwable $error, int $status, bool $development): string
    {
        $payload = [
            'status' => 'error',
            'id' => $id,
            'message' => $status >= 500 && ! $development
                ? 'An unexpected error occurred'
                : $error->getMessage(),
        ];

        if ($development) {
            $payload['details'] = [
                'type' => $error::class,
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'trace' => $error->getTraceAsString(),
            ];
        }

        return (string) json_encode($payload, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * @return array<int, array{num: int, code: string, hit: bool}>
     */
    private function snippet(string $file, int $line): array
    {
        if ($line < 1 || ! is_readable($file)) {
            return [];
        }

        $lines = file($file);

        if ($lines === false) {
            return [];
        }

        $pad = max(5, min(30, (int) config('error.development.snippet', 10)));
        $result = [];

        for ($number = max($line - $pad, 1); $number <= $line + $pad; $number++) {
            $code = $lines[$number - 1] ?? null;

            if ($code === null) {
                break;
            }

            $result[] = [
                'num' => $number,
                'code' => rtrim($code),
                'hit' => $number === $line,
            ];
        }

        return $result;
    }

    private function log(string $id, Throwable $error, RequestInterface $request, int $status): void
    {
        $path = (string) config('error.log.path', LOG . 'error.log');
        $traceFrom = (int) config('error.log.trace_from', 500);
        $uri = $request->getUri();
        $route = $uri->getPath();

        $record = [
            'at' => date(\DATE_ATOM),
            'id' => $id,
            'fingerprint' => substr(sha1(implode('|', [$error::class, $error->getFile(), (string) $error->getLine(), $route])), 0, 12),
            'status' => $status,
            'type' => $error::class,
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'method' => $request->getMethod(),
            'path' => $route,
            'query' => http_build_query($uri->getQuery()),
            'ip' => $this->address(),
            'ua' => $request->getHeader('User-Agent') ?? '',
            'referer' => $request->getHeader('Referer') ?? '',
        ];

        if ($status >= $traceFrom) {
            $record['trace'] = $error->getTraceAsString();
        }

        $json = json_encode($record, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($json === false) {
            return;
        }

        $directory = dirname($path);

        if ( ! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return;
        }

        if (file_put_contents($path, $json . \PHP_EOL, \FILE_APPEND | \LOCK_EX) === false) {
            error_log($json);
        }
    }

    private function wantsJson(RequestInterface $request): bool
    {
        if (strtolower((string) config('error.mode', 'html')) === 'json') {
            return true;
        }

        return str_contains(strtolower($request->getHeader('Accept') ?? ''), 'application/json');
    }

    private static function development(): bool
    {
        return config('app.environment', 'development') !== 'production';
    }

    private function address(): string
    {
        $address = $_SERVER['REMOTE_ADDR'] ?? null;

        return is_string($address) ? $address : '';
    }

    private static function id(): string
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    private static function plain(string $id): string
    {
        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>Error</title></head>'
            . '<body style="display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: sans-serif; text-align: center;">'
            . '<h1 style="font-size: 250%;">500</h1>'
            . '<p>'. $id .'</p>'
            . '</body></html>';
    }
}
