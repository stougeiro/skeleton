<?php declare(strict_types=1);

namespace Sourcerer\Error;

use Sourcerer\Error\Exception\HttpExceptionInterface;


/**
 * Translates a throwable into the HTTP answer: the status comes from the
 * exception itself when it is HTTP aware, otherwise from the configured type
 * map, falling back to 500. The view follows the status, so a new error type
 * is one view plus one config line.
 */
final readonly class ErrorMap
{
    /**
     * @param array<int, string> $views
     * @param array<class-string<\Throwable>, int> $types
     */
    public function __construct(
        private array $views,
        private array $types = [],
    ) { }


    public function status(\Throwable $error): int
    {
        if ($error instanceof HttpExceptionInterface) {
            return $error->status();
        }

        $candidates = [$error::class];

        foreach (class_parents($error) ?: [] as $parent) {
            $candidates[] = $parent;
        }

        foreach (class_implements($error) ?: [] as $interface) {
            $candidates[] = $interface;
        }

        foreach ($candidates as $candidate) {
            if (isset($this->types[$candidate])) {
                return $this->types[$candidate];
            }
        }

        return 500;
    }

    public function view(int $status): string
    {
        return $this->views[$status] ?? 'error:internal';
    }
}
