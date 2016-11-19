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

if (!function_exists('url')) {
    /**
     *  根据路由名称获取网址
     *
     * @param $routeName
     * @param array $params
     * @return string
     */
    function url($routeName, $params = [])
    {
        return \Kerisy\Mvc\Route\RouteMatch::getInstance()->url($routeName, $params);
    }
}

if (!function_exists('redis')) {
    /**
     *  获取redis 对象
     *
     * @return \Kerisy\Foundation\Storage\Redis
     */
    function redis()
    {
        return new \Kerisy\Foundation\Storage\Redis();
    }
}

if (!function_exists('config')) {
    /**
     *  config 对象
     *
     * @return mixed
     */
    function config()
    {
        return new \Kerisy\Config\Config();
    }
}

if (!function_exists('session')) {
    /**
     *  session 对象
     *
     * @return mixed
     */
    function session()
    {
        return \Kerisy\Foundation\Bootstrap\Session::getInstance();
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存对象
     * @return \Kerisy\Cache\Adapter\RedisCache;
     */
    function cache()
    {
        return new \Kerisy\Cache\Adapter\RedisCache();
    }
}

if (!function_exists('mcache')) {
    /**
     * 缓存对象
     * @return \Kerisy\Cache\Adapter\MemCache;
     */
    function mcache()
    {
        return new \Kerisy\Cache\Adapter\MemCache();
    }
}

if (!function_exists('syscache')) {
    /**
     * 缓存对象
     * @return \Kerisy\Cache\Adapter\ApcCache;
     */
    function syscache()
    {
        return new \Kerisy\Cache\Adapter\ApcCache();
    }
}


if (!function_exists('dump')) {
    /**
     * 输出
     * @return string;
     */
    function dump($str, $isReturn=false)
    {
        if(!$isReturn){
            return \Kerisy\Support\Log::show($str);
        }
        ob_start();
        \Kerisy\Support\Log::show($str);
        $msg = ob_get_clean();
        return $msg;
    }
}

if (!function_exists('debug')) {
    /**
     * 输出
     * @return string;
     */
    function debug($str, $isReturn=false)
    {
        if(!$isReturn){
            return \Kerisy\Support\Log::debug($str);
        }
        ob_start();
        \Kerisy\Support\Log::debug($str);
        $msg = ob_get_clean();
        return $msg;
    }
}

if (!function_exists('page404')) {
    /**
     * 404错误
     */
    function page404($str='')
    {
        throw new \Kerisy\Support\Exception\Page404Exception($str);
    }
}

if (!function_exists('throwExit')) {
    /**
     * 断点
     */
    function throwExit($str=null)
    {
        $str && dump($str);
        throw new \Kerisy\Support\Exception\RuntimeExitException("exit");
    }
}

if (!function_exists('l')) {
    /**
     * 多语言
     */
    function l($str, $params=[])
    {
        return \Kerisy\Support\Lang::get($str, $params);
    }
}

