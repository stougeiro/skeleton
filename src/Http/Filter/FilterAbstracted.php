<?php declare(strict_types=1);

    namespace STDW\Http\Filter;


	abstract class FilterAbstracted
	{
        abstract public function __invoke(array $params = []): void;
    }