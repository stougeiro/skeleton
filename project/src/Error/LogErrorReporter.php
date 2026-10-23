<?php declare(strict_types=1);

namespace Sourcerer\Error;

use STDW\Contract\Http\RequestInterface;


/**
 * Writes one JSON line per handled error to a single file, so repeated
 * behaviour can be found later by grouping on the fingerprint. The trace is
 * only stored from the configured status on, keeping the file readable when
 * scanners generate many 4xx.
 */
final class LogErrorReporter implements ErrorReporterInterface
{
    public function __construct(
        private readonly string $path,
        private readonly int $traceFrom = 500,
    ) { }


    public function report(string $id, \Throwable $error, RequestInterface $request, int $status): void
    {
        $path = $request->getUri()->getPath();

        $record = [
            'at' => date(\DATE_ATOM),
            'id' => $id,
            'fingerprint' => $this->fingerprint($error, $path),
            'status' => $status,
            'type' => $error::class,
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'method' => $request->getMethod(),
            'path' => $path,
            'query' => http_build_query($request->getUri()->getQuery()),
            'ip' => $this->address(),
            'ua' => $request->getHeader('User-Agent') ?? '',
            'referer' => $request->getHeader('Referer') ?? '',
        ];

        if ($status >= $this->traceFrom) {
            $record['trace'] = $error->getTraceAsString();
        }

        $json = json_encode($record, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($json === false) {
            return;
        }

        $directory = dirname($this->path);

        if ( ! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return;
        }

        if (file_put_contents($this->path, $json . \PHP_EOL, \FILE_APPEND | \LOCK_EX) === false) {
            error_log($json);
        }
    }

    private function fingerprint(\Throwable $error, string $path): string
    {
        $source = implode('|', [$error::class, $error->getFile(), (string) $error->getLine(), $path]);

        return substr(sha1($source), 0, 12);
    }

    private function address(): string
    {
        $address = $_SERVER['REMOTE_ADDR'] ?? null;

        return is_string($address) ? $address : '';
    }
}
