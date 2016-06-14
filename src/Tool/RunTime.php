<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/14
 */
namespace Kerisy\Tool;

class RunTime{
    static $elapsedTime=0;
    
    static function setStartTime(){
        self::$elapsedTime = self::getmicrotime();
    }
    
    static function runtime(){
        $now = self::getmicrotime();
        $time = $now-self::$elapsedTime;
        return number_format($time,3);
    }

    static function getmicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}