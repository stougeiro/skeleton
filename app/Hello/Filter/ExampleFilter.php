<?php declare(strict_types=1);

    namespace App\Hello\Filter;

    use STDW\Http\Filter\FilterAbstracted;


	final class ExampleFilter extends FilterAbstracted
	{
        public function __invoke(array $params = []): void
        { }
    }