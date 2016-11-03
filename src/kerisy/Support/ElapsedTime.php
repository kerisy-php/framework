<?php
/**
 *  runtime class
 *
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 下午1:25
 */

namespace Kerisy\Support;

class ElapsedTime
{
    // 时间属性
    static $elapsedTime = [];
    const SYS_START = "sys_start";

    /**
     *  设置开始时间
     */
    static function setStartTime($key = 0)
    {
        self::$elapsedTime[$key] = self::getmicrotime();
    }

    /**
     *  设置运行的时间
     *
     * @return mixed
     */
    static function runtime($key = 0)
    {
        $now = self::getmicrotime();
        $preTime = isset(self::$elapsedTime[$key])?self::$elapsedTime[$key]:-1;
        if($preTime==-1){
            $preTime = isset(self::$elapsedTime[self::SYS_START])?self::$elapsedTime[self::SYS_START]:0;
        }
        $time = $now - $preTime;
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
    
    public function __destruct()
    {
        Event::bind("clear", function () {
            self::$elapsedTime = [];
        });
    }

}