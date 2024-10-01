<?php declare(strict_types=1);

    namespace STDW\Database;

    use STDW\Database\Contract\DatabaseInterface;


	class Database extends DatabaseConnectionAbstracted implements DatabaseInterface
	{
        public static array $instances = [];


        public function __construct(?string $connection = null)
        {
            parent::__construct($connection);


            $instance = match($connection) {
                null => $this->connection,
                default => $connection,
            };

            static::$instances[$instance] = $this;
        }


        public function beginTransaction(): void
        {
            $this->db->beginTransaction();
        }

        public function commit(): void
        {
            $this->db->commit();
        }

        public function rollBack(): void
        {
            $this->db->rollBack();
        }


        public function fetch(string $sql, array $values = []): bool|object
        {
            $statement = $this->db->prepare($sql);

            foreach (array_values($values) as $k => $item) {
                $this->parameterIsValid($item);
                $statement->bindValue(($k+1), $item->value, $item->type);
            }

            $statement->execute();

            return $statement->fetch();
        }

        public function fetchAll(string $sql, array $values = []): array
        {
            $statement = $this->db->prepare($sql);

            foreach (array_values($values) as $k => $item) {
                $this->parameterIsValid($item);
                $statement->bindValue(($k+1), $item->value, $item->type);
            }

            $statement->execute();

            return $statement->fetchAll();
        }

        public function execute(string $sql, array $values = [], bool $safemode = true): bool
        {
            if ($safemode) {
                $this->beginTransaction();
            }


            $statement = $this->db->prepare($sql);

            foreach (array_values($values) as $k => $item) {
                $this->parameterIsValid($item);
                $statement->bindValue(($k+1), $item->value, $item->type);
            }

            $statement->execute();

            $affected = $statement->rowCount();


            if ( ! $safemode) {
                return ($affected > 0) ? true : false;
            }

            if ( ! $affected) {
                $this->rollBack();

                return false;
            }

            $this->commit();

            return true;
        }


        protected function parameterIsValid(mixed $parameter): void
        {
            if ( ! $parameter instanceof Param) {
                throw DatabaseException::invalidQueryParameter();
            }
        }
    }