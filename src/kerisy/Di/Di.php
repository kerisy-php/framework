<?php
/**
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

namespace Kerisy\Di;

use Kerisy\Config\Config;
use Kerisy\Di\Exception\DiNotDefinedException;

class Di
{
    /**
     *  Container instance
     *
     */
    protected static $containerInstance = null;

    /**
     * get Container
     *
     */
    public static function getContainer()
    {
        if (!self::$containerInstance) {
            $container = new Container();
            self::$containerInstance = $container;
        }
        return self::$containerInstance;
    }

    /**
     * register Container
     *
     * @param $name
     * @param $options
     * @param bool $isShare
     * @param bool $isLazy
     * @return mixed
     */
    private static function register($name, $options, $shared = true)
    {
        if (!$options) {
            throw new DiNotDefinedException(" container object is not found ~");
        }

        self::getContainer();

        if (is_string($options)) {
            return self::$containerInstance->set($name, $options, [], $shared);
        }else{
            $className = isset($options['class']) ? $options['class'] : null;
            $arguments = isset($options['arguments']) ? $options['arguments'] : null;
            $relate = isset($options['relate']) ? $options['relate'] : null;
            $configKey = isset($options['config']) ? $options['config'] : null;
            if(isset($options['share'])){
                $shared = isset($options['share']) ? $options['share'] : $shared;
            }
            $params = [];
            if($arguments){
                foreach ($arguments as $argument){
                    if(is_string($argument)){
                        if(isset($configKey[$argument])){
                            $argument = Config::get($configKey[$argument]);
                        }
                    }
                    $params[] = $argument;
                }
            }

            $relateArr = [];
            
            if($relate){
                foreach ($relate as $k=>$v){
                    $tmp = [];
                    $obj = self::get($k);
                    $tmp[] = $obj;
                    $tmp[] = $v;
                    $relateArr[] = $tmp;
                } 
            }
            
            return self::$containerInstance->set($name, $className, $params, $shared, $relateArr);
        }
    }

    /**
     * setting Container
     *
     * @param $name
     * @param $options
     * @return mixed
     */
    public static function set($name, $options)
    {
        return self::register($name, $options);
    }

    /**
     *  get a service
     *
     * @param $name
     * @return object
     * @throws \Trensy\Di\DiNotDefinedException
     */
    public static function get($name)
    {
        $service = self::$containerInstance->get($name);
        if (!$service) {
            throw new DiNotDefinedException(" Container is not defined ~");
        }
        return $service;
    }

    /**
     * 是否存在
     *
     * @param $name
     * @return bool
     */
    public static function has($name)
    {
        $service = self::$containerInstance->has($name);
        return $service?true:false;
    }

    /**
     * set a no share service
     *
     * @param $name
     * @param $options
     * @return null|Definition
     * @throws \Trensy\Di\DiNotDefinedException
     */
    public static function notShareSet($name, $options)
    {
        return self::register($name, $options, false);
    }

    public function __destruct()
    {
    }
}