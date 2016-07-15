<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/3
 * Time: 下午2:22
 */

namespace Kerisy\Database;


use Kerisy\Core\Exception;

class MySQLDriver implements DriverInterface
{
    /** Properties */
    protected $dsn;

    /** Constants */
    const DEFAULT_PORT = 3306;
    const PREFIX = 'mysql';
    const EXTENSION_NAME = 'pdo_mysql';

    public function __construct()
    {
        if (static::PREFIX === 'Unknown') {
            throw new Exception('Constant PREFIX need to be redefined in class.');
        }

        $this->dsn = new Dsn(static::PREFIX);
    }

    protected function getDsn(): Dsn
    {
        return $this->dsn;
    }

    public function connect(array $parameters, $username = null, $password = null, array $driverOptions = [])
    {
        $this->generateDSN($parameters);
        return new PDOConnection($this->getDsn(), $username, $password, $driverOptions);
    }

    /**
     * Generates the PDO DSN.
     * @param array $parameters The PDO DSN parameters
     */
    protected function generateDSN(array $parameters)
    {
        if (isset($parameters['dbname']) && !empty($parameters['dbname'])) {
            $this->getDsn()->setAttribute('dbname', $parameters['dbname']);
        }
        if (isset($parameters['host']) && !empty($parameters['host'])) {
            $this->getDsn()->setAttribute('host', $parameters['host']);
        }
        if (isset($parameters['port']) && !empty($parameters['port'])) {
            $this->getDsn()->setAttribute('port', $parameters['port']);
        }
        if (isset($parameters['charset']) && !empty($parameters['charset'])) {
            $this->getDsn()->setAttribute('charset', $parameters['charset']);
        }
    }
}