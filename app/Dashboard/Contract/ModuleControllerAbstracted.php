<?php declare(strict_types=1);

    namespace App\Dashboard\Contract;

    use STDW\Http\Controller\ControllerAbstracted;
    use App\Dashboard\Filter\ExampleFilter;


    abstract class ModuleControllerAbstracted extends ControllerAbstracted
    {
        protected static function filters(array $filters = []): array
        {
            return parent::filters( array_merge([
                ExampleFilter::class,
            ], $filters));
        }
    }