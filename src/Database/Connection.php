<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/3
 * Time: 上午11:11
 */

namespace Kerisy\Database;


use Kerisy\Core\Exception;
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
    protected $primary_key = 'id';


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

    public function getTable()
    {
        return $this->table;
    }

    public function setPrimaryKey($primary_key)
    {
        $this->primary_key = $primary_key;
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

    /**
     * Closes the connection with the database.
     */
    public function close()
    {
        $this->connected = false;
        $this->driverConnection = null; // Attention: Can not use setDriverConnection() here !
        $this->transactionLevel = 0;
//        $this->transactionIsolation = $this->getPlatform()->getDefaultTransactionIsolation();
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
        if ($this->getDriverConnection()->errorCode() !== '00000') {
            throw new Exception($this->getDriverConnection()->errorInfo()[2]);
        }
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
            $statement = $this->getDriverConnection();
            $affectedRows = $statement->exec($query);

        }
        $this->debugQuery($queryDebugger);
        if ($this->getDriverConnection()->errorCode() !== '00000') {
            throw new Exception($this->getDriverConnection()->errorInfo()[2]);
        }
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
        $table = $this->createQueryBuilder()->select()->from($this->table);
        $statement = $this->format($table)->execute();
        if ($statement) {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function fetchRow()
    {
        $table = $this->createQueryBuilder()->select()->from($this->table);
        $statement = $this->format($table)->execute();
        if ($statement) {
            return $statement->fetch(\PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function count()
    {
        $table = $this->createQueryBuilder()->select('count(*) as counter ')->from($this->table);
        $statement = $this->format($table)->execute();
        if ($statement) {
            $res = $statement->fetch(\PDO::FETCH_ASSOC);
            return $res['counter'];
        } else {
            return 0;
        }
    }

    public function format(QueryBuilder $table):QueryBuilder
    {
        if (empty($this->parts)) {
            return $table;
        }
        foreach ($this->parts as $part) {
            $condition = $part[0];
            if ($condition == 'where' && (count($part[1]) > 1)) {//兼容处理
                if (count($part[1]) == 2) {
                    is_string($part[1][1]) && $part[1][1] = " '{$part[1][1]}'";
                    $value = $part[1][0] . '=' . $part[1][1];
                } elseif (count($part[1]) == 3) {
                    is_string($part[1][2]) && $part[1][2] = " '{$part[1][2]}'";
                    $value = $part[1][0] . $part[1][1] . $part[1][2];
                }
            } else {
                $value = $part[1][0];
            }
            $table->$condition($value);

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

    public function update(array $update)
    {
        $table = $this->createQueryBuilder()->update($this->table);
        $table = $this->format($table);

        foreach ($update as $k => $v) {
            if ($v !== '') {
                if (is_string($v)) {
                    $v = " '$v' ";
                }
                $table->set($k, $v);
            }
        }
        return $table->execute();
    }

    public function insert(array $update)
    {
        $table = $this->createQueryBuilder()->insert($this->table);
        foreach ($update as $k => $v) {
            if ($v !== '') {
                if (is_string($v)) {
                    $v = " '$v' ";
                }
                $table->set($k, $v);
            }
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

    /**
     * Executes an SQL statement.
     * @param string $statement The statement to execute.
     * @return integer The number of affected rows.
     */
    public function exec($statement)
    {
        $queryDebugger = $this->createQueryDebugger($statement);
        $affectedRows = $this->getDriverConnection()->exec($statement);
        $this->debugQuery($queryDebugger);

        return $affectedRows;
    }

    public $transactionLevel = 0;

    /**
     * Starts a transaction.
     * @return boolean TRUE if the transaction has been started else FALSE.
     */
    public function beginTransaction()
    {
        $this->transactionLevel++;

        if ($this->transactionLevel === 1) {
            $queryDebugger = $this->createQueryDebugger('BEGIN TRANSACTION');
            $this->getDriverConnection()->beginTransaction();
            $this->debugQuery($queryDebugger);
        }
    }

    /**
     * Saves a transaction.
     * @return bool TRUE if the transaction has been saved else FALSE.
     * @throws ConnectionException
     */
    public function commit()
    {
        if ($this->transactionLevel === 0) {
            throw ConnectionException::noActiveTransaction();
        }

        if ($this->transactionLevel === 1) {
            $queryDebugger = $this->createQueryDebugger('COMMIT TRANSACTION');
            $this->getDriverConnection()->commit();
            $this->debugQuery($queryDebugger);
        }

        $this->transactionLevel--;
    }

    /**
     * Cancels a transaction.
     * @return bool TRUE if the transaction has been canceled else FALSE.
     * @throws ConnectionException
     */
    public function rollBack()
    {
        if ($this->transactionLevel === 0) {
            throw ConnectionException::noActiveTransaction();
        }

        if ($this->transactionLevel === 1) {
            $queryDebugger = $this->createQueryDebugger('ROLLBACK TRANSACTION');
            $this->getDriverConnection()->rollBack();
            $this->debugQuery($queryDebugger);
        }

        $this->transactionLevel--;
    }

    /**
     * Checks if a transaction has been started.
     * @return boolean TRUE if a transaction has been started else FALSE.
     */
    public function inTransaction()
    {
        return $this->transactionLevel !== 0;
    }


}
