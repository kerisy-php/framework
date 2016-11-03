<?php
/**
 * User: Peter Wang
 * Date: 16/10/21
 * Time: 下午3:42
 */

namespace Kerisy\Cache\Adapter;

use Kerisy\Cache\CacheInterface;

class ApcCache implements CacheInterface
{

    /**
     * 设置缓存
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value,$expire = 0 )
    {
        if(function_exists("apc_add")){
            $expire = $expire>0?$expire:0;
            if(apc_exists($key)) {
                return apc_store($key, $value, $expire);
            }
            return apc_add($key, $value, $expire);
        }else{
            if(function_exists("apcu_add")){
                $expire = $expire>0?$expire:0;
                if(apcu_exists($key)) {
                    $result = apcu_store($key, $value, $expire);
                    return $result;
                }
                $result = apcu_add($key, $value, $expire);
                return $result;
            }
        }
        return true;
    }

    /**
     * 获取缓存
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if(function_exists("apc_fetch")){
            $result = apc_fetch($key);
            if(!$result) return $default;
            return $result;
        }else{
            if(function_exists("apcu_fetch")){
                $result = apcu_fetch($key);
                if(!$result) return $default;
                return $result;
            }
        }
        return $default;
    }

    /**
     * 删除缓存
     *
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        if(function_exists("apc_delete")){
            return apc_delete($key);
        }else{
            if(function_exists("apcu_delete")){
                return apcu_delete($key);
            }
        }
        return true;
    }

    /**
     * 缓存是否存在
     *
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        if(function_exists("apc_exists")){
            return apc_exists($key);
        }else{
            if(function_exists("apcu_exists")){
                return apcu_exists($key);
            }
        }
        return false;
    }
}