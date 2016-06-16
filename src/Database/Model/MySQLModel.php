<?php
namespace Kerisy\Database\Model;

use Kerisy\Database\Configuration;
use Kerisy\Database\MySQLDriver;
use \Kerisy\Database\Connection;

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
class MySQLModel
{
    public $connection;

    public $configure;

    protected $table;

    public $debug;

    public function signton():Connection
    {
        $this->setDatabaseConfigure();

        $driver = new MySQLDriver();

        $configure = new Configuration($this->debug);

        $configure->setParameters($this->configure);

        $connection = (new Connection($driver, $configure))->setTable($this->table);

        return $connection;
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->signton(), $method], $parameters);
    }

    public function __callStatic($method, $parameters)
    {
        return call_user_func_array([$this->signton(), $method], $parameters);
    }


    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function setDatabaseConfigure()
    {
//        $this->configure = config('database')->get('mysql');
        $this->configure = [
            'host' => '10.1.11.166',
            'dbname' => 'putao_mall',
            'port' => 3306,
            'username' => 'root',
            'password' => '123456',
            'prefix' => 'mall_',
            'charset' => 'utf8'
        ];
    }


}