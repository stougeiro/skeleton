<?php declare(strict_types=1);

    namespace STDW\Http\Routing\Contract;


    interface ParserInterface
    {
        public function parse(string $uri): array;
    }