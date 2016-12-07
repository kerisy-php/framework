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
use Kerisy\Coroutine\Event;
use Kerisy\Foundation\Storage\Adapter\SQlAbstract as SQlAdapter;

class Pdo extends SQlAdapter
{
    private static $conn = [];
    protected $config = null;

    public function __construct($config=null)
    {
        $this->config = $config;
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
        if(self::$conn) return self::$conn;
        try {
            if (isset($config['master']) && !isset(self::$conn[self::CONN_MASTER])) {
                $masterConfig = $config['master'];
                $dbh = new \PDO($config['type'] . ':host=' . $masterConfig['host'] . ';port=' . $masterConfig['port'] . ';dbname=' . $masterConfig['db_name'] . '',
                    $masterConfig['user'], $masterConfig['password'],
                    array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$masterConfig['timeout']));
                self::$conn[self::CONN_MASTER] = $dbh;
                self::$conn[self::CONN_MASTER]->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                self::$conn[self::CONN_MASTER]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            if (isset($config['slave']) && !isset(self::$conn[self::CONN_MASTER])) {
                $slaveConfig = $config['slave'];
                $slaveDBH = new \PDO($config['type'] . ':host=' . $slaveConfig['host'] . ';port=' . $slaveConfig['port'] . ';dbname=' . $slaveConfig['db_name'] . '',
                    $slaveConfig['user'], $slaveConfig['password'],
                    array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$slaveConfig['timeout']));
                self::$conn[self::CONN_SLAVE] = $slaveDBH;
                self::$conn[self::CONN_SLAVE]->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                self::$conn[self::CONN_SLAVE]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }

        } catch (\PDOException $e) {
            throw $e;
        }

        if (!isset(self::$conn[self::CONN_MASTER])) {
            throw new \PDOException('master database server must set ~');
        }

        if (!isset(self::$conn[self::CONN_SLAVE])) {
            self::$conn[self::CONN_SLAVE] = self::$conn[self::CONN_MASTER];
        }

        return self::$conn;
    }

    /**
     *
     * 更新，插入，删除sql执行,只返回受影响的行数
     * @param unknown_type $sql
     * @param unknown_type $data
     */
    public function exec($sql, $connType = self::CONN_MASTER, $isInsert=false)
    {
        if (!$sql) {
            return false;
        }

        if (!(strtolower(substr($sql, 0, 6)) == 'insert' || strtolower(substr($sql, 0, 4)) == 'update'
            || strtolower(substr($sql, 0, 4)) == 'drop' || strtolower(substr($sql, 0, 4)) == 'delete'
            || strtolower(substr($sql, 0, 4)) == 'create'
            || strtolower(substr($sql, 0, 5)) == 'begin'
            || strtolower(substr($sql, 0, 6)) == 'commit'
            || strtolower(substr($sql, 0, 8)) == 'rollback'
        )
        ) {
            throw new \Exception("only run on select");
        }

        self::$_sql['sql'] = $sql;
        $func = $isInsert?"lastInsertId":"";
        return $this->set($sql, $connType, $func);
    }


    public function fetchAll($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetchAll";
        return $this->set($sql, $connType, $func);
    }


    public function fetch($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetch";
        return $this->set($sql, $connType, $func);
    }

    public function __destruct()
    {
        Event::bind("clear", function () {
            self::clearStaticData();
        });
    }


    protected function set($sql, $connType, $method)
    {
        try{
            $result = [];
            if (!$method || $method == 'lastInsertId') {
                self::$conn[$connType]->exec($sql);
                if ($method) {
                    $result = self::$conn[$connType]->$method();
                }
                if (self::$conn[$connType]->errorCode() != '00000') {
                    $error = self::$conn[$connType]->errorInfo();
                    $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                    throw new \Exception($errorMsg, self::$conn[$connType]->errorCode());
                }
                return $result;
            } else {
                $query = self::$conn[$connType]->query($sql);
//                dump('1');
//                dump($query);
                if($query === false){
                    throw new \Exception("server has gone away");
                }
                $result = $query->$method();
//                dump('2');
//                dump(self::$conn[$connType]->errorCode());
                if (self::$conn[$connType]->errorCode() != '00000') {
                    $error = self::$conn[$connType]->errorInfo();
                    $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                    throw new \Exception($errorMsg, self::$conn[$connType]->errorCode());
                }
                return $result;
            }
        }catch (\Error $e){
//            dump('3');
//            dump($e->getCode());
            if($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw new \Exception($e->getMessage());
            }
            //重新连接
            self::$conn = [];
            $this->conn();
//            dump('4');
//            dump(self::$conn);
            if(self::$conn){
                return $this->set($sql, $connType, $method);
            }else{
                throw new \Exception($e->getMessage());
            }
        }catch (\Exception $e){
//            dump('3');
//            dump($e->getCode());
            if($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw new \Exception($e->getMessage());
            }
            //重新连接
            self::$conn = [];
            $this->conn();
//            dump('4');
//            dump(self::$conn);
            if(self::$conn){
                return $this->set($sql, $connType, $method);
            }else{
                throw new \Exception($e->getMessage());
            }
        }
    }

}