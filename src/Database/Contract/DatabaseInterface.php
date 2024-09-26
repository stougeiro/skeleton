<?php declare(strict_types=1);

    namespace STDW\Database\Contract;


	interface DatabaseInterface
    {
        public function beginTransaction(): void;

        public function commit(): void;

        public function rollBack(): void;

        public function fetch(string $sql, array $values = []): bool|object;

        public function fetchAll(string $sql, array $values = []): array;

        public function execute(string $sql, array $values = [], bool $safemode = true): bool;
    }