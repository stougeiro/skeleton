<?php declare(strict_types=1);

    namespace STDW\Http\Routing\Contract;


    interface RouteInterface extends RouteCollectionInterface
    {
        public function listen(): void;
    }