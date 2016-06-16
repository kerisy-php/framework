<?php
namespace Kerisy\Database\Model;

use Kerisy\Database\Configuration;
use Kerisy\Database\PGDriver;
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
class PGModel
{
    public $connection;

    public $configure;

    protected $table;

    public $debug;

    public function signton():Connection
    {
        $this->setDatabaseConfigure();

        $driver = new PGDriver();

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
            'host' => 'localhost',
            'dbname' => 'putao_store',
            'port' => 5432,
            'username' => 'postgres',
            'password' => '1',
            'prefix' => 'mall_',
            'charset' => 'utf8'
        ];
    }


}