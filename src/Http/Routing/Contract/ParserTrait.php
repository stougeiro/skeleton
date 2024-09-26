<?php declare(strict_types=1);

    namespace STDW\Http\Routing\Contract;


    trait ParserTrait
    {
        private static function sanitize(string $uri): string
        {
            $uri = preg_replace('/\s/i', '', $uri);
            $uri = preg_replace('/\/{2,}/i', '/', $uri);
            $uri = trim($uri, '/');

            return $uri;
        }

        private static function count(string $uri, bool $isSanitized = true): int
        {
            if ( ! $isSanitized) {
                $uri = static::sanitize($uri);
            }

            if (strlen($uri)) {
                return count( explode('/', $uri));
            }

            return 0;
        }
    }