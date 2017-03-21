<?php
/**
 * 项目初始入口
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

use Kerisy\Di\Exception\InvalidArgumentException;

class Container
{
    protected $instance = [];
    protected $name = null;
    protected $ContainerData = [];

    public function has($name)
    {
        return isset($this->instance[$name]);
    }

    public function get($name)
    {
        if (!isset($this->ContainerData[$name]) || !$this->ContainerData[$name]) {
            throw new InvalidArgumentException("Container not exist!");
        }

        list($class, $params, $isShare, $relates) = $this->ContainerData[$name];

        if($isShare && isset($this->instance[$name])){
            return $this->instance[$name];
        }
        
        $class = new \ReflectionClass($class);
        $object = $class->newInstanceArgs($params);

        if($relates){
            foreach ($relates as $relate){
                if (!is_callable($relate)) {
                    throw new InvalidArgumentException(sprintf('The configure callable for class "%s" is not a callable.', get_class($object)));
                }
                call_user_func($relate, $object);
            }
        }

        if ($isShare && $object !== null) {
            $this->instance[$name] = $object;
        }
        return $object;
    }


    public function set($name, $class, $params=[], $isShare=true, $relates=[])
    {
        if (isset($this->ContainerData[$name])) {
            return true;
        }
        $this->ContainerData[$name] = [$class, $params, $isShare, $relates];
        return true;
    }
}