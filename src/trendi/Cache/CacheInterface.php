<?php
/**
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:06
 */

namespace Trendi\Cache;


interface CacheInterface
{

    /**
     * 设置缓存
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value);

    /**
     * 获取缓存
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * 删除缓存
     *
     * @param $key
     * @return mixed
     */
    public function del($key);
}