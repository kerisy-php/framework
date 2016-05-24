<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/11/29
 * Time: 11:04
 */
namespace Kerisy\Database;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Kerisy\Core\Object;


class DB extends Object
{
    public static $capsule = [];
    protected static $connection = 'default';


    public static function signton($default_connection = '')
    {
        $connection = $default_connection ?: static::$connection;

        if (!isset(self::$capsule[$connection])) {
            $config = config('database')->all();
            $capsule = new PTCapsule();
	    foreach($config as $key=>$value){
            	$capsule->addConnection($key, $value);
	    }
            $capsule->setEventDispatcher(new Dispatcher(new Container));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            self::$capsule[$connection] = $capsule->connection($connection);
        }
        return self::$capsule[$connection];
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([static::signton(), $method], $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([static::signton(), $method], $parameters);
    }

    public static function table($table, $connection = null)
    {
        $connection = $connection ?: static::$connection;
        return static::signton($connection)->table($table);
    }

}
