<?php declare(strict_types=1);

    namespace STDW\Http\Routing;

    use STDW\Http\Routing\Contract\ParserTrait;


    readonly class Uri
    {
        use ParserTrait;


        public static function get(): array
        {
            $uri = static::sanitize( request_uri());
            $parts = static::count($uri);

            return compact(
                'uri',
                'parts',
            );
        }
    }