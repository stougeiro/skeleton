<?php declare(strict_types=1);

    namespace STDW\Http\Routing\Contract;


    interface RouterInterface extends RouteCollectionInterface
    {
        public function listen(): void;
    }