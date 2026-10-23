<?php declare(strict_types=1);

namespace Sourcerer\Error;

use Sourcerer\Error\Exception\HttpExceptionInterface;

use STDW\Contract\Container\ContainerInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Contract\View\ViewManagerInterface;


/**
 * Turns any throwable into an HTTP response while keeping the response that
 * the pipeline had already built. The status comes from the exception or the
 * configured map, the body from the view of that status (development detail
 * for 5xx in development), and the full record from the reporter. Rendering
 * failures never leak and never recurse: they end in ErrorFallback.
 */
final class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ErrorMap $map,
        private readonly ErrorReporterInterface $reporter,
        private readonly ErrorSnippet $snippet,
        private readonly bool $development,
        private readonly string $mode,
        private readonly string $developmentView,
    ) { }


    public function handle(\Throwable $error, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = strtoupper(bin2hex(random_bytes(8)));
        $status = $this->map->status($error);
        $json = $this->wantsJson($request);

        $this->report($id, $error, $request, $status);

        $response = $response
            ->withStatus($status)
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Content-Type', $json ? 'application/json; charset=utf-8' : 'text/html; charset=utf-8');

        if ($status >= 500) {
            $response = $response->withHeader('X-Error-Id', $id);
        }

        if ($error instanceof HttpExceptionInterface) {
            foreach ($error->headers() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }

        try {
            $body = $json
                ? $this->jsonBody($id, $error, $status)
                : $this->htmlBody($id, $error, $status);

            return $response->withBody($body);
        }
        catch (\Throwable $render) {
            error_log('error handler: unable to render the error response: ' . $render->getMessage());

            return ErrorFallback::response($error, $response);
        }
    }


    private function htmlBody(string $id, \Throwable $error, int $status): string
    {
        $view = $this->map->view($status);

        if ($status >= 500 && $this->development) {
            $view = $this->developmentView;
        }

        /** @var ViewManagerInterface $views */
        $views = $this->container->get(ViewManagerInterface::class);

        return $views->render($view, $this->data($id, $error, $status));
    }

    /**
     * @return array<string, mixed>
     */
    private function data(string $id, \Throwable $error, int $status): array
    {
        $data = [
            'id' => $id,
            'status' => $status,
            'message' => $error->getMessage(),
        ];

        if ($status >= 500 && $this->development) {
            $data['type'] = $error::class;
            $data['file'] = $error->getFile();
            $data['line'] = $error->getLine();
            $data['trace'] = $error->getTraceAsString();
            $data['snippet'] = $this->snippet->lines($error->getFile(), $error->getLine());
        }

        return $data;
    }

    private function jsonBody(string $id, \Throwable $error, int $status): string
    {
        $payload = [
            'status' => 'error',
            'id' => $id,
            'message' => $status >= 500 && ! $this->development
                ? 'An unexpected error occurred'
                : $error->getMessage(),
        ];

        if ($this->development) {
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

    private function wantsJson(RequestInterface $request): bool
    {
        if (strtolower($this->mode) === 'json') {
            return true;
        }

        $accept = $request->getHeader('Accept') ?? '';

        return str_contains(strtolower($accept), 'application/json');
    }

    private function report(string $id, \Throwable $error, RequestInterface $request, int $status): void
    {
        try {
            $this->reporter->report($id, $error, $request, $status);
        }
        catch (\Throwable $failure) {
            error_log('error handler: unable to report the error: ' . $failure->getMessage());
        }
    }
}
