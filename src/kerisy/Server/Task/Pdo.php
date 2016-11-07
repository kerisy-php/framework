<?php
/**
 *  连接池连接pdo服务器
 *
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */
namespace Kerisy\Server\Task;

class Pdo
{
    const CONN_MASTER = 0;//写服务器
    const CONN_SLAVE = 1;//读服务器

    private static $conn = [];
    private static $config = [];


    public static function setConfig($config)
    {
        self::$config = $config;
    }

    public static function getConfig()
    {
        return self::$config;
    }

    /**
     *  执行
     * @param $sql
     * @param int $dnType
     * @param string $method  fetch , fetchAll
     * @return null
     * @throws \Exception
     */
    public function perform($sql, $dnType = self::CONN_MASTER, $method = "")
    {
        if (!$sql) return null;
        $conn = $this->setConn($dnType);
        if (!$method || $method=='lastInsertId') {
            $conn->exec($sql);
            if($method){
                return $conn->$method();
            }
            if ($conn->errorCode() != '00000') {
                $error = $conn->errorInfo();
                throw new \Exception('ERROR: [' . $error['1'] . '] ' . $error['2']);
            }
        } else {
            $result = $conn->query($sql);
            if ($conn->errorCode() != '00000') {
                $error = $conn->errorInfo();
                throw new \Exception('ERROR: [' . $error['1'] . '] ' . $error['2']);
            }
            return $result->$method();
        }
    }

    /**
     * 获取连接
     * 
     * @param int $dnType
     * @return mixed
     */
    protected function setConn($dnType = self::CONN_MASTER)
    {
        if (self::$conn && isset(self::$conn[$dnType])) {
            return self::$conn[$dnType];
        }

        try {
            if (isset(self::$config['master']) && !isset(self::$conn[self::CONN_MASTER])) {
                $masterConfig = self::$config['master'];
                $dbh = new \PDO(self::$config['type'] . ':host=' . $masterConfig['host'] . ';port=' . $masterConfig['port'] . ';dbname=' . $masterConfig['db_name'] . '', 
                    $masterConfig['user'], $masterConfig['password'], 
                       array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$masterConfig['timeout']));
                self::$conn[self::CONN_MASTER] = $dbh;
            }
            if (isset(self::$config['slave']) && !isset(self::$conn[self::CONN_MASTER])) {
                $slaveConfig = self::$config['slave'];
                $slaveDBH = new \PDO(self::$config['type'] . ':host=' . $slaveConfig['host'] . ';port=' . $slaveConfig['port'] . ';dbname=' . $slaveConfig['db_name'] . '', 
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

    public function __destruct()
    {
    }
}
