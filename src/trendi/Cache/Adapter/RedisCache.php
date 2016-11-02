<?php
/**
 * redis k/v 缓存
 *
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:08
 */

namespace Trendi\Cache\Adapter;

use Trendi\Cache\CacheInterface;
use Trendi\Foundation\Storage\Redis;

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