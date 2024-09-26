<?php declare(strict_types=1);

    namespace STDW\Database;

    use STDW\Schema\Schema;
    use STDW\Support\Str;
    use PDO;


	abstract class DatabaseConnectionAbstracted
	{
        private static array $stored_connections = [];

        protected string $connection = 'default';

        protected PDO $db;


        protected function __construct(?string $connection = null)
        {
            $this->db = $this->getConnection($connection);
        }


        protected function getConnection(?string $connection = null): PDO
        {
            $connection = ( ! Str::empty($connection)) ? $connection : $this->connection;

            if ( ! in_array($connection, array_keys(self::$stored_connections)))
            {
                $config = config('database.connection.'.$connection);

                $schema = new Schema([
                    'driver' => 'string',
                    'host' => 'string',
                    'port' => 'int',
                    'schema' => 'string',
                    'username' => 'string',
                    'password' => 'string',
                ]);

                if ( ! $schema->validate($config)) {
                    throw DatabaseException::invalidSchema();
                }

                if ( ! SupportedDrivers::exists($config['driver'])) {
                    throw DatabaseException::invalidDriverConfiguration($config['driver'], SupportedDrivers::list());
                }


                $port = $config['port'];

                if ( ! empty($port)) {
                    $port = ';port='. $port;
                }


                $dsn = $config['driver'].":host=".$config['host'].$port.";dbname=".$config['schema'];
                $user = $config['username'];
                $password = $config['password'];

                $options = [
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                ];


                self::$stored_connections[$connection] = new PDO(
                    $dsn,
                    $user,
                    $password,
                    $options
                );
            }

            return self::$stored_connections[$connection];
        }
    }