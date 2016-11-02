<?php
/**
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午2:22
 */

namespace Trendi\Di;


interface DiInterface
{

    public static function set($name, $options);

    public static function get($name);

    public static function setNoShare($name, $options);

}