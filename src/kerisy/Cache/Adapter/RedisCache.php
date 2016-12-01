<?php
/**
 * redis k/v 缓存
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

namespace Kerisy\Cache\Adapter;

use Kerisy\Cache\CacheInterface;
use Kerisy\Foundation\Storage\Redis;
use Kerisy\Support\Serialization\Serialization;

class RedisCache implements CacheInterface
{

    /**
     * 获取缓存
     * 
     * @param $key
     * @param null $default
     * @return null
     */
    public function get($key, $default = null)
    {
        $obj = new Redis();
        $result = $obj->get($key);
        if (!$result) return $default;
        $result = Serialization::get()->xtrans($result);
        return $result;
    }


    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param int $expire  过期时间 单位s
     * @return mixed
     */
    public function set($key, $value, $expire = -1)
    {
        $obj = new Redis();
        
        $value = Serialization::get()->trans($value);
        
        if ($expire > 0) {
            $result = $obj->setex($key, $expire, $value);
        } else {
            $result = $obj->set($key, $value);
        }
        return $result;
    }

    /**
     * 删除key
     * 
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        $obj = new Redis();
        return $obj->del($key);
    }

}