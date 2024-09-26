<?php declare(strict_types=1);

    namespace STDW\Database;

    use STDW\Database\Contract\ModelInterface;
    use STDW\Database\Param;
    use STDW\Support\Str;
    use PDO;


	abstract class ModelAbstracted extends Database implements ModelInterface
	{
        protected array $where = [];

        protected string $table;


        public function scope(array $where): void
        {
            $this->where = array_merge($this->where, $where);
        }


        public function total(array $where = [], bool $debug = false): int
        {
            $where = $this->where( array_merge($this->where, $where));

            $sql = "
                SELECT
                    count(*) as total
                FROM
                    ".$this->table; if ( ! Str::empty($where['sql'])) { $sql .= "
                WHERE
                    ".$where['sql']; }

            if ($debug) {
                debug($sql, true, true);
            }


            $statement = $this->db->prepare($sql);

            foreach ($where['values'] as $k => $param) {
                $statement->bindValue(($k+1), $param->value, $param->type);
            }

            $statement->execute();

            return ($statement->fetch())->total;
        }

        public function create(array $data, bool $debug = false): ?int
        {
            if (empty($data)) {
                return null;
            }


            $sql = "
                INSERT
                    INTO ".$this->table." (".implode(', ', array_keys($data)).")
                    VALUES (?".str_repeat(', ?', (count($data)-1)).")";

            if ($debug) {
                debug($sql, true, true);
            }


            $this->db->beginTransaction();

                $statement = $this->db->prepare($sql);

                foreach (array_values($data) as $k => $param) {
                    $statement->bindValue(($k+1), $param->value, $param->type);
                }

                $statement->execute();

                $affected = $statement->rowCount();

                if ( ! $affected) {
                    $this->db->rollBack();

                    return null;
                }

                $id = $this->db->lastInsertId();

            $this->db->commit();

            return (int) $id;
        }

        public function read(array $where = [], bool $debug = false): bool|object
        {
            $where = $this->where( array_merge($this->where, $where));

            $sql = "
                SELECT *
                FROM
                    ".$this->table; if ( ! Str::empty($where['sql'])) { $sql .= "
                WHERE
                    ".$where['sql']; }

            if ($debug) {
                debug($sql, true, true);
            }


            $statement = $this->db->prepare($sql);

            foreach ($where['values'] as $k => $param) {
                $statement->bindValue(($k+1), $param->value, $param->type);
            }

            $statement->execute();

            return $statement->fetch();
        }

        public function list(
            ?int $limit = null,
            ?int $offset = null,
            array $where = [],
            array $order = [],
            bool $debug = false ): array
        {
            $where = $this->where( array_merge($this->where, $where));
            $order = $this->order($order);
 
            $sql = "
                SELECT *
                FROM
                    ".$this->table; if ( ! Str::empty($where['sql'])) { $sql .= "
                WHERE
                    ".$where['sql']; } if ( ! Str::empty($order)) { $sql .= "
                ORDER BY
                    ".$order; } if ($limit > 0) { $sql .= "
                LIMIT ? "; } if ($limit > 0 && $offset >= 0) { $sql .= "
                OFFSET ?"; }

            if ($debug) {
                debug($sql, true, true);
            }


            $statement = $this->db->prepare($sql);

            if (is_int($limit) && $limit > 0 && is_null($offset)) {
                $where['values'][] = Param::int($limit);
            } else if (is_int($limit) && $limit > 0 && is_int($offset) && $offset >= 0) {
                $where['values'][] = Param::int($limit);
                $where['values'][] = Param::int($offset);
            }

            foreach ($where['values'] as $k => $param) {
                $statement->bindValue(($k+1), $param->value, $param->type);
            }

            $statement->execute();

            return $statement->fetchAll();
        }

        public function all(?int $limit = null, ?int $offset = null, bool $debug = false): array
        {
            return $this->list($limit, $offset, [], [], $debug);
        }

        public function update(array $data, array $where, bool $debug = false): bool
        {
            if (empty($data) || empty($where)) {
                return false;
            }


            $set = [];
            $where = $this->where( array_merge($this->where, $where));

            foreach (array_keys($data) as $key) {
                $set[] = $key.' = ?';
            }

            $sql = "
                UPDATE
                    ".$this->table."
                SET
                    ".implode(', ', $set)."
                WHERE
                    ".$where['sql'];

            if ($debug) {
                debug($sql, true, true);
            }


            $this->db->beginTransaction();

                $statement = $this->db->prepare($sql);
                $values = array_merge( array_values($data), $where['values']);

                foreach ($values as $k => $param) {
                    $statement->bindValue(($k+1), $param->value, $param->type);
                }

                $statement->execute();

                $affected = $statement->rowCount();

                if ( ! $affected) {
                    $this->db->rollBack();

                    return false;
                }

            $this->db->commit();

            return true;
        }

        public function delete(array $where, bool $debug = false): bool
        {
            if (empty($where)) {
                return false;
            }


            $where = $this->where( array_merge($this->where, $where));

            $sql = "
                DELETE FROM
                    ".$this->table."
                WHERE
                    ".$where['sql'];

            if ($debug) {
                debug($sql, true, true);
            }


            $this->db->beginTransaction();

                $statement = $this->db->prepare($sql);

                foreach ($where['values'] as $k => $param) {
                    $statement->bindValue(($k+1), $param->value, $param->type);
                }

                $statement->execute();

                $affected = $statement->rowCount();

                if ( ! $affected) {
                    $this->db->rollBack();

                    return false;
                }

            $this->db->commit();

            return true;
        }


        protected function where(array $where): array
        {
            $sql = '';
            $values = [];

            if ( ! count($where)) {
                return compact('sql', 'values');
            }


            $items = [];

            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $items[] = $value;
                } else if (is_string($key)) {
                    $items['and'][$key] = $value;
                }
            }

            $i = 0;
            $j = 0;
            $where = array_values($items);

            foreach ($where as $collection) {
                if ($j > 0) {
                    $sql .= ' OR ';
                }

                $sql .= '(';

                foreach ($collection as $key => $value) {
                    if ($i > 0) {
                        $sql .= ' AND ';
                    }

                    // Parameter checker
                    $this->parameterIsValid($value);

                    if ($value->type == PDO::PARAM_NULL) {
                        $sql .= $key.' IS NULL';
                    } else {
                        $sql .= $key.' = ?';
                        $values[] = $value;
                    }

                    $i++;
                }

                $sql .= ')';

                $i=0;
                $j++;
            }

            return compact('sql', 'values');
        }

        protected function order(array $order): string
        {
            if ( ! count($order)) {
                return '';
            }


            $i = 0;
            $sql = '';

            foreach ($order as $key => $value) {
                if ($i > 0) {
                    $sql .= ', ';
                }

                if ( ! is_int($key)) {
                    $sql .= $key . ($value == 'DESC' ? ' DESC' : '');
                } else {
                    $sql .= $value;
                }

                $i++;
            }

            return $sql;
        }
    }