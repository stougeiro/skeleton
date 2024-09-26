<?php declare(strict_types=1);

    namespace STDW\Database\Contract;


	interface ModelInterface
	{
        public function create(array $data, bool $debug = false): ?int;

        public function read(array $where = [], bool $debug = false): bool|object;

        public function list(?int $limit = null, ?int $offset = null, array $where = [], array $order = [], bool $debug = false): array;

        public function update(array $data, array $where, bool $debug = false): bool;

        public function delete(array $where, bool $debug = false): bool;
    }