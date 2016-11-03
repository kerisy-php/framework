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
use Kerisy\Foundation\Exception\ConfigNotFoundException;
use Kerisy\Foundation\Storage\Adapter\SQlAbstract as SQlAdapter;
use Kerisy\Pool\PoolClient;
use Kerisy\Coroutine\Event;
use Kerisy\Support\Log;

class Pdo extends SQlAdapter
{
    const CONN_MASTER = 0;//写服务器
    const CONN_SLAVE = 1;//读服务器

    //适配器类型
    const ADAPTER_DEFAULT = "default";
    const ADAPTER_POOL = "pool";

    private $type = self::ADAPTER_DEFAULT;

    protected static $client = null;
    private static $conn = [];
    
    public function __construct()
    {
        $type = Config::get("app.adapter.database");
        $this->type = $type;
        if($this->type == self::ADAPTER_DEFAULT){
            $this->initializeDefault();
        }else{
            $this->initializePool();
        }

        parent::__construct();
    }

    protected function initializeDefault()
    {
        $this->prefix = Config::get("storage.pdo.prefix");
    }

    protected function initializePool()
    {
        if(self::$client) return;

        $config = Config::get("client.pool");
        if (!$config) {
            throw new ConfigNotFoundException("client.pool not config");
        }
        $prefix = isset($config['pdo']['prefix']) ? $config['pdo']['prefix'] : null;
        if (!$prefix) {
            $prefix = Config::get("storage.pdo.prefix");
        }

        $this->prefix = $prefix;

//        Log::sysinfo("new pdo client conn");
        self::$client = new PoolClient($config['host'], $config['port'], $config['serialization'],$config);
    }


    protected function setConn($dnType = self::CONN_MASTER)
    {
        if (self::$conn && isset(self::$conn[$dnType])) {
            return self::$conn[$dnType];
        }

        try {
            $config = Config::get("storage.pdo");
            if (isset($config['master']) && !isset(self::$conn[self::CONN_MASTER])) {
                $masterConfig = $config['master'];
                $dbh = new \PDO($config['type'] . ':host=' . $masterConfig['host'] . ';port=' . $masterConfig['port'] . ';dbname=' . $masterConfig['db_name'] . '',
                    $masterConfig['user'], $masterConfig['password'],
                    array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$masterConfig['timeout']));
                self::$conn[self::CONN_MASTER] = $dbh;
            }
            if (isset($config['slave']) && !isset(self::$conn[self::CONN_MASTER])) {
                $slaveConfig = $config['slave'];
                $slaveDBH = new \PDO($config['type'] . ':host=' . $slaveConfig['host'] . ';port=' . $slaveConfig['port'] . ';dbname=' . $slaveConfig['db_name'] . '',
                    $slaveConfig['user'], $slaveConfig['password'],
                    array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$slaveConfig['timeout']));
                self::$conn[self::CONN_SLAVE] = $slaveDBH;
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

        self::$conn[$dnType]->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        self::$conn[$dnType]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return self::$conn[$dnType];
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
            || strtolower(substr($sql, 0, 4)) == 'create')
        ) {
            throw new \Exception("only run on select , show");
        }

        self::$_sql[] = $sql;

        if($this->type == self::ADAPTER_DEFAULT){
            $conn = $this->setConn($connType);
            $conn->exec($sql);
            if($isInsert){
                return $conn->lastInsertId(); 
            }else{
                return true;
            }
        }else{
            $method = "";
            $isInsert && $method = "lastInsertId";
            $params = [
                $sql,
                $connType,
                $method
            ];
            $data = self::$client->get("pdo", $params);
            return $data;
        }
    }


    public function fetchAll($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }
        self::$_sql[] = $sql;

        if($this->type == self::ADAPTER_DEFAULT){
            $conn = $this->setConn($connType);
            $query = $conn->query($sql);
            return $query->fetchAll();
        }else{
            $params = [
                $sql,
                $connType,
                "fetchAll"
            ];
            $data = self::$client->get("pdo", $params);
            return $data; 
        }
    }


    public function fetch($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }
        self::$_sql[] = $sql;

        if($this->type == self::ADAPTER_DEFAULT){
            $conn = $this->setConn($connType);
            $query = $conn->query($sql);
            return $query->fetch();
        }else{
            $params = [
                $sql,
                $connType,
                "fetch"
            ];
            $data = self::$client->get("pdo", $params);
            return $data; 
        }
    }


    public function __destruct()
    {
        Event::bind("clear", function () {
            self::clearStaticData();
        });
//        self::$client->close();
    }
}