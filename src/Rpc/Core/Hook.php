<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/7
 */
namespace Kerisy\Rpc\Core;

class Hook{

    private static $instance;

    private $hooks = array();

    private function __construct() {}
    private function __clone() {}

    public static function add($hook_name, $fn)
    {
        $instance = self::get_instance();
        $instance->hooks[$hook_name] = $fn;
    }

    public static function fire($hook_name, $params = null)
    {
        $instance = self::get_instance();
        if (isset($instance->hooks[$hook_name])) {
                return call_user_func_array($instance->hooks[$hook_name], array(&$params));
        }
    }

    public static function get_instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Hook();
        }
        return self::$instance;
    }

}