<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/16
 * Time: 下午5:27
 */

namespace Kerisy\Database\Model;

use Kerisy\Database\Configuration;
use \Kerisy\Database\Connection;


abstract class Model
{

    public static $connection;
    public $configure;

    protected $table;

    public $debug = false;
    public $primary_key = 'id';


    public function signton():Connection
    {
        if (is_null(static::$connection)) {
            $this->setDatabaseConfigure();

            $driver = $this->getDriver();
            $configure = new Configuration($this->debug);

            $configure->setParameters($this->configure);
            static::$connection = new Connection($driver, $configure);
        }
        static::$connection->setTable($this->table);

        return static::$connection;
    }


    abstract public function getDriver();

    abstract public function setDatabaseConfigure();

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->signton(), $method], $parameters);
    }


    static public function __callStatic($method, $parameters)
    {
        $static = new static;
        return call_user_func_array([$static, $method], $parameters);
    }


    public function setDebug($debug)
    {
        $this->debug = $debug;
    }


}
