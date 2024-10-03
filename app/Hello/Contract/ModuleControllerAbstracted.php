<?php declare(strict_types=1);

    namespace App\Hello\Contract;

    use STDW\Http\Controller\ControllerAbstracted;
    use App\Hello\Filter\ExampleFilter;


    abstract class ModuleControllerAbstracted extends ControllerAbstracted
    {
        protected static function filters(array $filters = []): array
        {
            return parent::filters( array_merge([
                ExampleFilter::class,
            ], $filters));
        }
    }