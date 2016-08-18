<?php
/**
 *   运行时间
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/14
 */
namespace Kerisy\Tool;

class RunTime
{
    // 时间属性
    static $elapsedTime = [];

    /**
     *  设置开始时间
     */
    static function setStartTime($key=0)
    {

        self::$elapsedTime[$key] = self::getmicrotime();
    }

    /**
     *  设置运行的时间
     *
     * @return mixed
     */
    static function runtime($key=0)
    {
        $now = self::getmicrotime();
        $time = $now - self::$elapsedTime[$key];
        return $time;
    }

    /**
     *  获取时间戳
     *
     * @return float
     */
    static function getmicrotime()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}