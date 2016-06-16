<?php
namespace Kerisy\Database\Model;

use Kerisy\Database\PGDriver;

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
class PGModel extends Model
{
    public function getDriver()
    {
        return new PGDriver();
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