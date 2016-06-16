<?php
namespace Kerisy\Database\Model;

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

    public function getDriver()
    {
        return new MySQLDriver();
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