<?php
/**
 *  laravel Facade support
 *
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 上午11:10
 */

namespace Trendi\Support;

use Trendi\Support\Exception\RuntimeException;

class Facade
{

    protected static $app;

    /**
     *  facade object list
     * @var array
     */
    protected static $_facadeInstance = [];

    /**
     *  must duplicate
     *
     * @throws RuntimeException
     */
    protected static function setFacadeAccessor()
    {
        throw new RuntimeException(" must set Facade name ~");
    }

    /**
     *  get facade object
     *
     * @return object
     */
    protected static function getFacadeRoot()
    {
        $name = static::setFacadeAccessor();

        if (is_object($name)) return $name;

        $name = strtolower($name);

        if (isset(static::$_facadeInstance[$name])) {
            return static::$_facadeInstance[$name];
        }
        $object = self::$app->get($name);

        static::$_facadeInstance[$name] = $object;

        return $object;
    }


    /**
     *  set DI
     *
     * @param $app
     */
    public static function setFacadeApplication($app)
    {
        self::$app = $app;
    }


    public static function getFacadeApplication()
    {
        return self::$app;
    }

    /**
     * reset
     */
    public static function clearFacadeInstances()
    {
        static::$_facadeInstance = [];
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }

}