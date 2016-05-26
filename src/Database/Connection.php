<?php
/**
 * 葡萄科技.
 * @copyright Copyright (c) 2015 Putao Inc.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 16/5/25 10:28
 */

namespace Kerisy\Database;

use Kerisy;
use Kerisy\Core\Object;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Connection is often used as an application component and configured in the application
 * configuration like the following:
 *
 * ~~~
 * 'components' => [
 *     'db' => [
 *         'class' => '\Kerisy\DB\Connection',
 *         'connnection_name' => 'default',
 *     ],
 * ],
 *
 * OR
 *
 * 'components' => [
 *     'db' => [
 *         'class' => '\Kerisy\DB\Connection',
 *         'connnection_name' => 'default',
 *         'config' => [
 *              'driver' => 'mysql',
 *              'port'=>1234,
 *              'host' => 'mysql1.putao.io',
 *              'database' => 'putao_ttsj',
 *              'username' => 'putao_toycenter',
 *              'password' => 'fjR0zSCfGWzIP',
 *              'prefix' => 'pt_',
 *              'charset' => 'utf8',
 *              'collation' => 'utf8_unicode_ci',
 *         ]
 *     ],
 * ],
 *
 */
class Connection extends Object
{
    public $connection_name = 'default';

    public $conntection = null;

    public $config = null;

    public function init()
    {
        
    }

    public function getConnection()
    {
        if ($this->conntection == null) {
            $this->config === null && $this->config = Kerisy::$app->config('database')->all($this->connection_name);
            if (!$this->config || !is_array($this->config)) {
                throw  new Kerisy\Core\InvalidConfigException('Database Config Not Defined');
            }

            $capsule = new Capsule();
            $capsule->setEventDispatcher(new Dispatcher(new Container));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            $capsule->addConnection($this->config, $this->connection_name);
            $this->conntection = $capsule->connection($this->connection_name);
        }
        return $this->conntection;
    }
}