<?php
/**
 * redis 集群连接方法
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/16
 */
namespace Kerisy\Support;
use Predis\Autoloader;
use Predis\Client;
class RedisCluster{

    static function init()
    {
        Autoloader::register();
        $serversObj = config("rediscluster");
        $servers =$serversObj->all();
        $options = ['cluster' => 'redis'];
        return new Client($servers, $options);
    }

}