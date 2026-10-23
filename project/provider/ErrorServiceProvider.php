<?php declare(strict_types=1);

namespace App\Provider;

use Sourcerer\Error\ErrorHandler;
use Sourcerer\Error\ErrorHandlerInterface;
use Sourcerer\Error\ErrorMap;
use Sourcerer\Error\ErrorSnippet;
use Sourcerer\Error\LogErrorReporter;
use STDW\Contract\Container\ContainerInterface;
use STDW\Contract\Container\ServiceProviderAbstracted;


/**
 * Error handling: the map status → view, the reporter and the handler, all
 * built from config/error.php. The handler receives the container and only
 * resolves the view manager when rendering, so it survives a partially booted
 * application.
 */
final class ErrorServiceProvider extends ServiceProviderAbstracted
{
    public function register(): void
    {
        $this->container->singleton(ErrorHandlerInterface::class, static function (ContainerInterface $container): ErrorHandlerInterface {
            /** @var array{
             *     environment: string,
             *     mode: string,
             *     views: array<int, string>,
             *     types: array<class-string<\Throwable>, int>,
             *     development: array{view: string, snippet: int},
             *     log: array{path: string, trace_from: int}
             * } $config */
            $config = config('error');

            return new ErrorHandler(
                $container,
                new ErrorMap($config['views'], $config['types']),
                new LogErrorReporter($config['log']['path'], $config['log']['trace_from']),
                new ErrorSnippet($config['development']['snippet']),
                $config['environment'] !== 'production',
                $config['mode'],
                $config['development']['view'],
            );
        });
    }
}
