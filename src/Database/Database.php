<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Database
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Database
{

    static private $_config = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'kerisy',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ];

    public function __construct($config = [])
    {
        self::connect($config);
    }

    public function connect($config)
    {
        $config += self::$_config;
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => $config['driver'],
            'host' => $config['localhost'],
            'database' => $config['database'],
            'username' => $config['user'],
            'password' => $config['password'],
            'charset' => $config['charset'],
            'collation' => $config['collation'],
            'prefix' => $config['prefix'],
        ]);
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

}
