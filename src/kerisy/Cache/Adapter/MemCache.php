<?php
/**
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
use Kerisy\Foundation\Storage\Memcached;

class MemCache implements CacheInterface
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
        $obj = new Memcached();
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
    public function set($key, $value, $expire = 0)
    {
        $obj = new Memcached();
        $result = $obj->set($key, $value, $expire);
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
        $obj = new Memcached();
        return $obj->delete($key);
    }

}