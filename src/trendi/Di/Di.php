<?php
/**
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午3:22
 */

namespace Trendi\Di;

use Trendi\Di\Base\ContainerBuilder;
use Trendi\Di\Base\Definition;
use Trendi\Di\Exception\DiNotDefinedException;

class Di implements DiInterface
{

    const DEFINE_SHARE = true;
    const DEFINE_NO_SHARE = false;

    /**
     *  Container instance
     *
     * @var \Trendi\Di\Base\ContainerBuilder
     */
    protected static $containerInstance = null;


    /**
     * Di constructor.
     *
     */
    public static function setContainerInstance()
    {
        if (!self::$containerInstance) {
            $container = new ContainerBuilder();
            self::$containerInstance = $container;
        }
    }

    /**
     * get Container
     *
     * @return \Trendi\Di\Base\ContainerBuilder
     */
    public static function getContainer()
    {
        return self::$containerInstance;
    }

    /**
     * register Container
     *
     * @param $name
     * @param $options
     *
     *     [
     *      "class"=>"AppBundle\Mail\NewsletterManager",
     *      "arguments"=>new Reference('mailer')
     *      "configurator"=>array(new Reference('app.email_configurator'), 'configure')
     *      ]
     *
     * @param bool $isShare
     * @param bool $isLazy
     * @return null|\Trendi\Di\Base\Definition
     */
    private static function register($name, $options, $shared = true, $lazy = true)
    {

        if (!$options) {
            throw new DiNotDefinedException(" container object is not found ~");
        }

        self::setContainerInstance();

        $defineObj = null;

        if (is_string($options)) {
            $definition = new Definition($options);
            $definition->setShared($shared);
            $definition->setLazy($lazy);
            $defineObj = self::$containerInstance->setDefinition($name, $definition);
        } elseif (is_array($options)) {
            $className = isset($options['class']) ? $options['class'] : null;
            $arguments = isset($options['arguments']) ? $options['arguments'] : null;
            $configurator = isset($options['configurator']) ? $options['configurator'] : null;
            $autowire = isset($options['autowire']) ? $options['autowire'] : null;
            $autowiringTypes = isset($options['autowiring_types']) ? $options['autowiring_types'] : null;
            $shared = isset($options['shared']) ? $options['shared'] : true;

            if (!$className) {
                throw new DiNotDefinedException(" Container object is not found ~");
            }
            $definition = new Definition($className);

            if ($autowire) $definition->setAutowired(true);
            if ($arguments) $definition->addArgument($arguments);
            if ($configurator) $definition->setConfigurator($configurator);
            if ($autowiringTypes) $definition->setAutowiringTypes($autowiringTypes);
            $definition->setShared($shared);
            $definition->setLazy($lazy);

            $defineObj = self::$containerInstance->setDefinition($name, $definition);
        }

        return $defineObj;
    }

    /**
     * setting Container
     *
     * @param $name
     * @param $options
     * @return null|\Trendi\Di\Base\Definition
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
     * @throws \Trendi\Di\DiNotDefinedException
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
     * set a no share service
     *
     * @param $name
     * @param $options
     * @return null|Definition
     * @throws \Trendi\Di\DiNotDefinedException
     */
    public static function setNoShare($name, $options)
    {
        return self::register($name, $options, self::DEFINE_NO_SHARE);
    }

    public function __destruct()
    {
    }
}