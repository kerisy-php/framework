<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/3
 * Time: 上午11:11
 */

namespace Kerisy\Database;


use Kerisy\Database\Expression\ExpressionBuilder;
use Kerisy\Database\Debug\QueryDebugger;
use Kerisy\Database\Event\Events;
use Kerisy\Database\Event\QueryDebugEvent;

class Connection
{
    protected $pdo;
    protected $connected;

    protected $expressionBuilder;

    protected $configuration;

    protected $table;

    protected $driver;

    protected $configure;

    protected $driverConnection;


    public function __construct(DriverInterface $driver, Configuration $configuration)
    {
        $this->setDriver($driver);

        $this->setExpressionBuilder(new ExpressionBuilder());

        $this->getConfiguration = $this->setConfiguration($configuration);

        $this->close();
    }

    public function setTable($table)
    {
        $this->table = $this->getConfiguration()->getParameter('prefix') . $table;
        return $this;
    }

    public function setDriver(DriverInterface $driver):Connection
    {
        $this->driver = $driver;
        return $this;
    }

    public function getDriver():DriverInterface
    {
        return $this->driver;
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getConfiguration():Configuration
    {
        return $this->configuration;
    }

    public function setExpressionBuilder(ExpressionBuilder $expressionBuilder)
    {
        $this->expressionBuilder = $expressionBuilder;

        return $this;
    }

    public function getExpressionBuilder(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }

    public function createQueryBuilder():QueryBuilder
    {
        return new QueryBuilder($this);
    }

    public function setDriverConnection(DriverConnectionInterface $driverConnection):Connection
    {
        $this->driverConnection = $driverConnection;

        return $this;
    }

    public function getDriverConnection():DriverConnectionInterface
    {
        $this->connect();

        return $this->driverConnection;
    }


    public function connect()
    {
        if ($this->isConnected()) {
            return true;
        }
        //todo 尝试再次连接;
        $this->setDriverConnection(
            $this->getDriver()->connect(
                $this->getConfiguration()->getParameters(),
                $this->getUsername(),
                $this->getPassword()
            )
        );
        $this->connected = true;
        return true;
    }


    public function close()
    {
        $this->connected = false;
    }

    public function isConnected()
    {
        return $this->connected;
    }

    /*
     * @param $query select * from on_hand where id =1  ||  SELECT * FROM foo WHERE id IN (:foo)
     * @param $parameters = ['foo'=>[1,2,3],'bar'=>1];
     * @param $types: array('foo' => 'integer[]','bar'=>'integer')
     */
    public function executeQuery($query, array $parameters = [], array $types = [])
    {
        $queryDebugger = $this->createQueryDebugger($query, $parameters, $types);
        if (!empty($parameters)) {
            $statement = $this->getDriverConnection()->prepare($query);

            if (!empty($types)) {
                $this->bindStatementParameters($statement, $parameters, $types);
                $statement->execute();
            } else {
                $statement->execute($parameters);
            }
        } else {
            $statement = $this->getDriverConnection()->query($query);

        }
        $this->debugQuery($queryDebugger);

        return $statement;
    }

    public function executeUpdate($query, array $parameters = [], array $types = [])
    {

        $queryDebugger = $this->createQueryDebugger($query, $parameters, $types);

        if (!empty($parameters)) {
            list($query, $parameters, $types) = $this->queryRewrite($query, $parameters, $types);

            $statement = $this->getDriverConnection()->prepare($query);

            if (!empty($types)) {
                $this->bindStatementParameters($statement, $parameters, $types);
                $statement->execute();
            } else {
                $statement->execute($parameters);
            }
            $affectedRows = $statement->rowCount();
        } else {
            $affectedRows = $this->getDriverConnection()->exec($query);
        }

        $this->debugQuery($queryDebugger);

        return $affectedRows;
    }


    public function query(...$params)
    {
        $queryDebugger = $this->createQueryDebugger($params[0]);
        $statement = call_user_func_array([$this->getDriverConnection(), 'query'], $params);
        $this->debugQuery($queryDebugger);

        return $statement;
    }

    public function queryRewrite($query, $parameters, $types)
    {
        return [$query, $parameters, $types];
    }

    public function bindStatementParameters(\PDOStatement $statement, $parameters, $types)
    {
        foreach ($parameters as $key => $parameter) {
            if (is_int($key)) {
                $placeholder = $key + 1;
            } else {
                $placeholder = ':' . $key;
            }

            if (isset($types[$key])) {
                list($parameter, $type) = $this->convertToDatabase($parameter, $types[$key]);
                $statement->bindValue($placeholder, $parameter, $type);
            } else {
                $statement->bindValue($placeholder, $parameter);
            }
        }
        return $statement;
    }

    public function convertToDatabase($parameter, $type)
    {
        return [$parameter, $type];
    }

    /**
     * Creates a query debugger if the query needs to be debugged.
     * @param string $query The query to debug.
     * @param array $parameters The query parameters to debug.
     * @param array $types The query parameter types to debug.
     * @return QueryDebugger|null The query debugger or null if the query does need to be debugged.
     */
    protected function createQueryDebugger($query, array $parameters = [], array $types = [])
    {
        if ($this->getConfiguration()->getDebug()) {
            return new QueryDebugger($query, $parameters, $types);
        }

        return null;
    }

    /**
     * Debugs a query.
     * @param QueryDebugger|null $queryDebugger The query debugger.
     * @return null
     */
    protected function debugQuery(QueryDebugger $queryDebugger = null)
    {
        if ($queryDebugger === null) {
            return null;
        }

        $queryDebugger->stop();

        $this->getConfiguration()->getEventDispatcher()->dispatch(
            Events::QUERY_DEBUG,
            new QueryDebugEvent($queryDebugger)
        );
    }

    public function fetchAll()
    {
        $table = $this->createQueryBuilder()->from($this->table);
        $table = $this->format($table);
        return $table->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchRow()
    {
        $table = $this->createQueryBuilder()->from($this->table);
        $table = $this->format($table);
        return $table->execute()->fetch(\PDO::FETCH_ASSOC);

    }

    public function format(QueryBuilder $table):QueryBuilder
    {
        if (empty($this->parts)) {
            return $table;
        }
        foreach ($this->parts as $part) {
            $condition = $part[0];
            $table->$condition($part[1][0]);
        }
        unset($this->parts);

        return $table;
    }

    protected $parts = [];

    public function __call($name, $args)
    {
        $this->parts[] = [$name, $args];
        return $this;
    }

    public function update($where, array $update)
    {
        $table = $this->createQueryBuilder()->where($where)->update($this->table);

        foreach ($update as $k => $v) {
            $table->set($k, $v);
        }
        return $table->execute();
    }

    public function insert(array $update)
    {
        $table = $this->createQueryBuilder()->insert($this->table);
        foreach ($update as $k => $v) {
            $table->set($k, $v);
        }
        return $table->execute();
    }

    public function delete(string $where)
    {
        if (empty($where)) {
            return false;
        }
        return $this->createQueryBuilder()->delete($this->table)->where($where)->execute();
    }


    /**
     * Gets the username from the parameters.
     * @return string|null The username if it is defined else NULL.
     */
    public function getUsername()
    {
        return $this->getConfiguration()->getParameter('username');
    }

    /**
     * Sets the username as parameter.
     * @param string|null $username The username (NULL to remove it).
     * @return Connection
     */
    public function setUsername($username)
    {
        $this->getConfiguration()->setParameter('username', $username);

        return $this;
    }

    /**
     * Gets the password from the parameters.
     * @return string|null The password if it is defined else NULL.
     */
    public function getPassword()
    {
        return $this->getConfiguration()->getParameter('password');
    }

    /**
     * Sets the password as parameter.
     * @param string|null $password The password (NULL to remove it).
     * @return Connection
     */
    public function setPassword($password)
    {
        $this->getConfiguration()->setParameter('password', $password);

        return $this;
    }

    /**
     * Sets the database name as parameter.
     * @param string|null $database The database name (NULL to remove it).
     * @return Connection
     */
    public function setDatabase($database)
    {
        $this->getConfiguration()->setParameter('dbname', $database);

        return $this;
    }

    /**
     * Gets the host from the parameters.
     * @return string|null The host if it is defined else NULL.
     */
    public function getHost()
    {
        return $this->getConfiguration()->getParameter('host');
    }

    /**
     * Sets the host as parameter.
     * @param string|null $host The host (NULL to remove it).
     * @return Connection
     */
    public function setHost($host)
    {
        $this->getConfiguration()->setParameter('host', $host);

        return $this;
    }

    /**
     * Gets the port from the parameters.
     * @return integer|null The port if it is defined else NULL.
     */
    public function getPort()
    {
        return $this->getConfiguration()->getParameter('port');
    }

    /**
     * Sets the port as parameter.
     * @param integer|null $port The port (NULL to remove it).
     * @return Connection
     */
    public function setPort($port)
    {
        $this->getConfiguration()->setParameter('port', $port);

        return $this;
    }

}