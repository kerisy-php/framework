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

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $connection = 'default';

    private static $_capsules;

    public function __construct(array $attributes = [])
    {
        if (!self::$_capsules[$this->connection]) {
            $this->initORM();
        }

        parent::__construct($attributes);
    }

    public function initORM()
    {
        $config = config('database')->get($this->connection);
        $capsules = new Capsule;
        $capsules->addConnection($config, $this->connection);
        $capsules->setEventDispatcher(new Dispatcher(new Container));
        $capsules->setAsGlobal();
        $capsules->bootEloquent();
        self::$_capsules[$this->connection] = $capsules;
    }
}
