<?php
/**
 * redis pool 连接 redis服务器
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Pool\Task;

use Predis\Client;

class Redis
{
    private static $config = [];
    private static $client = null;

    public static function setConfig($config)
    {
        self::$config = $config;
    }

    public static function getConfig()
    {
        return self::$config;
    }

    /**
     * 执行
     * 
     * @return mixed
     * @throws \Exception
     */
    public function perform()
    {
        $params = func_get_args();

        if (!isset($params[0])) {
            throw new \Exception("Invalid argument!");
        }
        $cmd = $params[0];
        $servers = self::$config['servers'];
        $options = self::$config['options'];

        try {
            if(!self::$client){
                self::$client = new Client($servers, $options);
            }
            if (isset($params[1])) {
                return self::$client->$cmd(...$params[1]);
            } else {
                return self::$client->$cmd();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}