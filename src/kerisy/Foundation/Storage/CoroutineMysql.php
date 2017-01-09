<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Storage;

use Config;
use Kerisy\Foundation\Storage\Adapter\SQlAbstract as SQlAdapter;

class CoroutineMysql extends SQlAdapter
{
    protected static $conn = null;
    protected $config = null;

    public function __construct($storageConfig = null)
    {
        $this->config = $storageConfig;
        $this->conn();
    }

    protected function conn()
    {
        if(!self::$conn){
            if(!$this->config){
                $this->config = Config::get("storage.server.pdo");
            }
            self::$prefix = $this->config['prefix'];
            parent::__construct();
            $this->initConn($this->config);
        }
    }


    protected function initConn($config)
    {
        if (class_exists("\\Swoole\\Coroutine\\MySQL")) {
            try {
                $swoole_mysql = new \Swoole\Coroutine\MySQL();
                if (isset($config['master']) && !isset(self::$conn[self::CONN_MASTER])) {
                    $masterConfig = $config['master'];
                    $host = $masterConfig['host'];
                    $user = $masterConfig['user'];
                    $pass = $masterConfig['password'];
                    $port = $masterConfig['port'];
                    $dataBase = $masterConfig['db_name'];
                    $swoole_mysql->connect(['host' => $host, 'user' => $user, 'password' => $pass, "port" => $port, 'database' => $dataBase]);
                    $swoole_mysql->timeOut = $masterConfig['timeout'];

                    self::$conn[self::CONN_MASTER] = $swoole_mysql;
                }

                if (isset($config['slave']) && !isset(self::$conn[self::CONN_MASTER])) {
                    $slaveConfig = $config['slave'];
                    $host = $slaveConfig['host'];
                    $user = $slaveConfig['user'];
                    $pass = $slaveConfig['password'];
                    $port = $slaveConfig['port'];
                    $dataBase = $slaveConfig['db_name'];
                    $swoole_mysql->connect(['host' => $host, 'user' => $user, 'password' => $pass, "port" => $port, 'database' => $dataBase]);
                    $swoole_mysql->timeOut = $slaveConfig['timeout'];

                    self::$conn[self::CONN_SLAVE] = $swoole_mysql;
                }
            }catch (\PDOException $e){
                throw $e;
            }

            if (!isset(self::$conn[self::CONN_MASTER])) {
                throw new \Exception('master database server must set ~');
            }

            if (!isset(self::$conn[self::CONN_SLAVE])) {
                self::$conn[self::CONN_SLAVE] = self::$conn[self::CONN_MASTER];
            }

            return self::$conn;

        } else {
            throw new \Exception("\\Swoole\\Coroutine\\MySQL not Support!");
        }
    }

    /**
     *
     * 更新，插入，删除sql执行,只返回受影响的行数
     * @param unknown_type $sql
     * @param unknown_type $data
     */
    public function exec($sql, $connType = self::CONN_MASTER, $isInsert = false)
    {
        if (!$sql) {
            return false;
        }

        if (!(strtolower(substr($sql, 0, 6)) == 'insert' || strtolower(substr($sql, 0, 6)) == 'update'
            || strtolower(substr($sql, 0, 4)) == 'drop' || strtolower(substr($sql, 0, 6)) == 'delete'
            || strtolower(substr($sql, 0, 6)) == 'create'
            || strtolower(substr($sql, 0, 5)) == 'begin'
            || strtolower(substr($sql, 0, 6)) == 'commit'
            || strtolower(substr($sql, 0, 8)) == 'rollback'
        )
        ) {
            throw new \Exception("only run on select , show");
        }

        self::$_sql['sql'] = $sql;
        $func = $isInsert ? "lastInsertId" : "";
        return $this->set($sql, $connType, $func);
    }


    public function fetchAll($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetchAll";
        $result = $this->set($sql, $connType, $func);dump($result);
        return $result;
    }


    public function fetch($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetch";
        $result = $this->set($sql, $connType, $func);dump($result);
        return $result;
    }

    protected function set($sql, $connType, $method)
    {
        try{
            $result = [];

            if (!$method || $method == 'lastInsertId') {
                self::$conn[$connType]->query($sql, self::$conn[$connType]->timeOut);
                if ($method) {
                    $result = self::$conn[$connType]->insert_id;
                }
                if (self::$conn[$connType]->errno != '0') {
                    $error = self::$conn[$connType]->errorInfo();
                    $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                    throw new \Exception($errorMsg, self::$conn[$connType]->errno);
                }
                return $result;
            } else {
                $result = self::$conn[$connType]->query($sql, self::$conn[$connType]->timeOut);
                if($result === false){
                    throw new \Exception("server has gone away");
                }
                if($method == 'fetch'){
                    $result = current($result);
                }
                if (self::$conn[$connType]->errno != '0') {
                    $error = self::$conn[$connType]->errorInfo();
                    $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                    throw new \Exception($errorMsg, self::$conn[$connType]->errno);
                }
                return $result;
            }
        }catch (\Error $e){
            if($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw new \Exception($e->getMessage());
            }
            //重新连接
            self::$conn = [];
            $this->conn();
            if(self::$conn){
                return $this->set($sql, $connType, $method);
            }else{
                throw new \Exception($e->getMessage());
            }
        }catch (\Exception $e){
            if($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw new \Exception($e->getMessage());
            }
            //重新连接
            self::$conn = [];
            $this->conn();
            if(self::$conn){
                return $this->set($sql, $connType, $method);
            }else{
                throw new \Exception($e->getMessage());
            }
        }
    }
}