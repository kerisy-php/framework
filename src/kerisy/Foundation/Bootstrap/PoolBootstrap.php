<?php
/**
 *  连接池初始化
 * 
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午3:38
 */

namespace Kerisy\Foundation\Bootstrap;

use Kerisy\Config\Config;
use Kerisy\Pool\Task\Pdo;
use Kerisy\Pool\Task\Redis;

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