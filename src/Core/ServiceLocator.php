<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Core
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Core;

use Kerisy\Di\Container;

/**
 * Class ServiceLocator
 *
 * @package Kerisy\Core
 */
class ServiceLocator extends Object
{
    /**
     * Bind a service definition to this service locator.
     *
     * @param $id
     * @param array $definition
     * @param boolean $replace Replace existing services.
     * @throws InvalidConfigException
     */
    public function bind($id, $definition = [], $replace = true)
    {
        $container = Container::getInstance();
        if (!$replace && $container->has($id)) {
            throw new InvalidParamException("Can not bind service, service '$id' is already exists.");
        }

        if (is_array($definition) && !isset($definition['class'])) {
            throw new InvalidConfigException("The configuration for the \"$id\" service must contain a \"class\" element.");
        }

        $container->setSingleton($id, $definition);
    }

    public function unbind($id)
    {
        Container::getInstance()->clear($id);
    }

    public function has($id)
    {
        return Container::getInstance()->has($id);
    }

    /**
     * Get a service by it's id.
     *
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return Container::getInstance()->get($id);
    }

    /**
     * Call the given callback or class method with dependency injection.
     *
     * @param $callback
     * @param array $arguments
     * @return mixed
     */
    public function call($callback)
    {
        return call_user_func($callback);
    }

    protected function getCallerReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }

        if (is_array($callback)) {
            return new \ReflectionMethod($callback[0], $callback[1]);
        }

        return new \ReflectionFunction($callback);
    }

    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        } else {
            return parent::__get($name);
        }
    }


    public function __isset($name)
    {
        if ($this->has($name)) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }
}
