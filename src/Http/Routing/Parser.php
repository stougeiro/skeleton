<?php declare(strict_types=1);

    namespace STDW\Http\Routing;

    use STDW\Http\Routing\Contract\ParserInterface;
    use STDW\Http\Routing\Contract\ParserTrait;
    use Throwable;


    class Parser implements ParserInterface
    {
        use ParserTrait;


        private array $placeholders = [];


        public function __construct()
        {
            try {
                $placeholders = config('routing.placeholders');
            } catch (Throwable $e) { }

            foreach ($placeholders as $placeholder => $regex) {
                $this->placeholders[$placeholder] = $regex;
            }
        }


        public function parse(string $routemap): array
        {
            $routemap = static::sanitize($routemap);
            $parts = static::count($routemap);

            $route = preg_replace_callback('/\{(\w+):(\w+)\}/', function ($matches) {
                $param = $matches[1];
                $type = $matches[2];
                $regex = $this->replace($type);

                return "(?P<{$param}>{$regex})";
            }, $routemap);

            $route = '/^' . str_replace('/', '\/', $route) . '$/';

            return compact(
                'routemap',
                'parts',
                'route',
            );
        }


        private function replace(string $type): string
        {
            foreach ($this->placeholders as $placeholder => $regex) {
                $type = str_replace($placeholder, '('.$regex.')', $type);
            }

            return $type;
        }
    }