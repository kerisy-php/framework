<?php
namespace Kerisy\Database\Model;

use Kerisy\Database\Configuration;
use \Kerisy\Database\Connection;
use Kerisy\Database\MySQLDriver;

/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/3
 * Time: 下午1:49
 *
 *  SELECT select_list
 *   FROM table_expression
 *   [ ORDER BY ... ]
 *   [ LIMIT { number | ALL } ] [ OFFSET number ]
 *
 */
class MySQLModel extends Model
{
    static public $connection = null;

    public function getDriver()
    {
        return new MySQLDriver();
    }

    public function setDatabaseConfigure()
    {
//        $this->configure = config('database')->get('mysql');
    }


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

    public function lastInsertId()
    {
        $this->lastInsertId();
    }


}