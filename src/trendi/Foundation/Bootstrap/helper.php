<?php
/**
 * User: Peter Wang
 * Date: 16/9/9
 * Time: 下午12:18
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
        return \Trendi\Mvc\Route\RouteMatch::getInstance()->url($routeName, $params);
    }
}

if (!function_exists('redis')) {
    /**
     *  获取redis 对象
     *
     * @return \Trendi\Foundation\Storage\Redis
     */
    function redis()
    {
        return new \Trendi\Foundation\Storage\Redis();
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
        return new \Trendi\Config\Config();
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
        return \Trendi\Foundation\Bootstrap\Session::getInstance();
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存对象
     * @return \Trendi\Cache\Adapter\RedisCache;
     */
    function cache()
    {
        return new \Trendi\Cache\Adapter\RedisCache();
    }
}

if (!function_exists('syscache')) {
    /**
     * 缓存对象
     * @return \Trendi\Cache\Adapter\ApcCache;
     */
    function syscache()
    {
        return new \Trendi\Cache\Adapter\ApcCache();
    }
}


if (!function_exists('dump')) {
    /**
     * 缓存对象
     * @return \Trendi\Support\Log;
     */
    function dump($str)
    {
        $str = print_r($str, true);
        return \Trendi\Support\Log::debug($str);
    }
}

if (!function_exists('page404')) {
    /**
     * 404错误
     */
    function page404($str='')
    {
        throw new \Trendi\Support\Exception\Page404Exception($str);
    }
}

if (!function_exists('throwExit')) {
    /**
     * 404错误
     */
    function throwExit()
    {
        throw new \Trendi\Support\Exception\RuntimeExitException("exit");
    }
}

