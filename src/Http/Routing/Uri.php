<?php declare(strict_types=1);

    namespace STDW\Http\Routing;

    use STDW\Http\Routing\Contract\ParserTrait;


    readonly class Uri
    {
        use ParserTrait;


        public string $uri;

        public int $parts;


        public function __construct(string $uri, int $parts)
        {
            $this->uri = $uri;
            $this->parts = $parts;
        }


        public static function get(): Uri
        {
            $uri = static::sanitize( request_uri());
            $parts = static::count($uri);

            return new Uri($uri, $parts);
        }
    }