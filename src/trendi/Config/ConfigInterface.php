<?php
/**
 *  config
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午7:47
 */

namespace Trendi\Config;


interface ConfigInterface
{
    public static function set($name, $value);

    public static function get($name);
}