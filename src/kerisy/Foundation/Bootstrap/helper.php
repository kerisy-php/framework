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
     * @param $groupName
     * @return string
     */
    function url($routeName, $params = [], $groupName='')
    {
        return \Kerisy\Mvc\Route\RouteMatch::getInstance()->simpleUrl($routeName, $params, $groupName);
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
     * @return \Kerisy\Storage\Cache\Adapter\RedisCache;
     */
    function cache()
    {
        return new \Kerisy\Storage\Cache\Adapter\RedisCache();
    }
}

if (!function_exists('mcache')) {
    /**
     * 缓存对象
     * @return \Kerisy\Storage\Cache\Adapter\MemCache;
     */
    function mcache()
    {
        return new \Kerisy\Storage\Cache\Adapter\MemCache();
    }
}

if (!function_exists('syscache')) {
    /**
     * 缓存对象
     * @return \Kerisy\Storage\Cache\Adapter\ApcCache;
     */
    function syscache()
    {
        return new \Kerisy\Storage\Cache\Adapter\ApcCache();
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
            $data = debug_backtrace(2, 2);
            $line = isset($data[0])?$data[0]:null;
            $func = isset($data[1])?$data[1]:null;
            if($func){
                \Kerisy\Support\Log::show("{$func['function']}(): {$line['file']} . (line:{$line['line']})");
            }
            else{
                \Kerisy\Support\Log::show(" {$line['file']} . (line:{$line['line']})");
            }
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
        if(!$str){
            list($line, $func) = debug_backtrace(2, 2);
            \Kerisy\Support\Log::show("{$func['function']}(): {$line['file']} . (line:{$line['line']})");
        }
        
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

if (!function_exists('array_isset')) {
    /**
     * isset 
     */
    function array_isset($arr, $key, $default=null)
    {
        return isset($arr[$key]) ? $arr[$key]:$default;
    }
}

if (!function_exists('trans')) {
    /**
     * isset
     */
    function trans($arr)
    {
        return  \Kerisy\Support\Serialization\Serialization::get()->trans($arr);
    }
}

if (!function_exists('xtrans')) {
    /**
     * isset
     */
    function xtrans($arr)
    {
        return  \Kerisy\Support\Serialization\Serialization::get()->xtrans($arr);
    }
}

if (!function_exists('responseEnd')) {
    /**
     * isset
     */
    function responseEnd($callback)
    {
        \Kerisy\Coroutine\Event::bind("request.end",$callback);
    }
}


//non-blocking
if (!function_exists('nonBlock')) {
    /**
     * 非阻塞程序处理
     */
    function nonBlock($callback,$interval=1)
    {
        \Kerisy\Support\Timer::after($interval,$callback);
    }
}

