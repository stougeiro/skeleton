<?php declare(strict_types=1);

    namespace STDW\Database;

    use STDW\ListObject\ListObjectAbstracted;


    final class SupportedDrivers extends ListObjectAbstracted
    {
        protected static array $collection = [
            'mysql',
        ];
    }