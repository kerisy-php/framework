<?php
/**
 *  连接池初始化
 * 
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Bootstrap;

use Kerisy\Config\Config;
use Kerisy\Server\Task\Pdo;
use Kerisy\Server\Task\Redis;

class PoolBootstrap
{
    protected static $instance = null;

    /**
     *  instance
     * @return \Kerisy\Foundation\Bootstrap\RouteBootstrap
     */
    public static function getInstance()
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self();
    }

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->load();
    }

    /**
     *  pdo , redis 连接池配置导入
     */
    public function load()
    {
        $config = Config::get("storage.pdo");
        if ($config) Pdo::setConfig($config);
        $config = Config::get("storage.redis");
        if ($config) Redis::setConfig($config);
    }
}