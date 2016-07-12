<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/7/8
 */
namespace Kerisy\Job;

class Model{

    function getDb()
    {
        $config = config('database')->get('job');
        $connection = new \PDO($config['driver'].':host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] . '', $config['username'], $config['password'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
        $connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $connection;
    }
}