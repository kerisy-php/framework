<?php
/**
 * User: wangkaihui
 * Date: 16/8/11
 * Time: 上午10:24
 */

namespace Kerisy\Core;


class Hook
{

    private static $instance;

    private $hooks = array();
    private $hookReturn = array();

    private function __construct()
    {
    }

    private function __clone()
    {
    }


    public static function add($hook_name, $fn)
    {
        $instance = self::getInstance();
        $instance->hooks[$hook_name][] = $fn;
    }

    public static function fire($hook_name, $params = null)
    {
        $instance = self::getInstance();
        $result = null;
        if (isset($instance->hooks[$hook_name])) {
            foreach ($instance->hooks[$hook_name] as $fn) {
                $result = call_user_func_array($fn, array(&$params));
                $instance->hookReturn[$hook_name][] = $result;
            }
        }
        return $result;
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Hook();
        }
        return self::$instance;
    }

    public static function getReturn($hook_name)
    {
        $instance = self::getInstance();
        return isset($instance->hookReturn[$hook_name]) ? $instance->hookReturn[$hook_name] : "";
    }
}