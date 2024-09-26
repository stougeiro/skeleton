<?php declare(strict_types=1);

    namespace STDW\Database;

    use STDW\Database\DatabaseException;
    use PDO;


    final class Param
    {
        public function __construct(
            readonly public bool|null|int|string $value,
            readonly public int $type)
        {
            if ( ! in_array($type, [
                PDO::PARAM_BOOL,
                PDO::PARAM_NULL,
                PDO::PARAM_INT,
                PDO::PARAM_STR,
            ])) {
                throw DatabaseException::unsupportedParameterType();
            }
        }


        public static function bool(bool $value): Param
        {
            return new Param($value, PDO::PARAM_BOOL);
        }

        public static function null(): Param
        {
            return new Param(null, PDO::PARAM_NULL);
        }

        public static function int(int $value): Param
        {
            return new Param($value, PDO::PARAM_INT);
        }

        public static function str(string $value): Param
        {
            return new Param($value, PDO::PARAM_STR);
        }
    }