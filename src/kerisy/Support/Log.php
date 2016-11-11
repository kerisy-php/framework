<?php
/**
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
namespace Kerisy\Support;


class Log
{
    private static $callback = null;
    /**
     * 颜色初始化
     *
     * @param $foreground_colors
     * @param $background_colors
     */
    protected static function init()
    {
        // Set up shell colors
        $foreground_colors['black'] = '0;30';
        $foreground_colors['dark_gray'] = '1;30';
        $foreground_colors['blue'] = '0;34';
        $foreground_colors['light_blue'] = '1;34';
        $foreground_colors['green'] = '0;32';
        $foreground_colors['light_green'] = '1;32';
        $foreground_colors['cyan'] = '0;36';
        $foreground_colors['light_cyan'] = '1;36';
        $foreground_colors['red'] = '0;31';
        $foreground_colors['light_red'] = '1;31';
        $foreground_colors['purple'] = '0;35';
        $foreground_colors['light_purple'] = '1;35';
        $foreground_colors['brown'] = '0;33';
        $foreground_colors['yellow'] = '1;33';
        $foreground_colors['light_gray'] = '0;37';
        $foreground_colors['white'] = '1;37';
        return $foreground_colors;
    }


    protected static function preData()
    {
        $ip = swoole_get_local_ip();
        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");

        $result = [];
        $result[] = date('Y-m-d H:i:s');
        $result[] = posix_getpid();
        $result[] = current($ip);
        $result[] = $elapsedTime;
        return $result;
    }


    public static function register($callback)
    {
        self::$callback = $callback;
    }

    protected static function outPut($type, $data)
    {
        if (self::$callback && (self::$callback instanceof \Closure)) {
            $closureParam = [$type,$data];
            return call_user_func(self::$callback, $closureParam);
        }else{
            $msg = array_pop($data);
            if($type) $data[] = $type;
            if($type == 'show'){
                $string = $msg;
            }else{
                $string = "[".implode("][",$data)."] ".$msg;
            }
            $foreground_colors = self::init();
            $color = [
                "info"=>"light_gray",
                "sysinfo"=>"dark_gray",
                "warn"=>"yellow",
                "debug"=>"green",
                "show"=>"green",
                "error"=>"red",
            ];

            if (isset($foreground_colors[$color[$type]])) {
                $colorStr = $foreground_colors[$color[$type]];
                $string = "\033[" . $colorStr . "m".$string;
            }
            $string = $string . "\033[0m\n";
            echo $string;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        $msg = isset($arguments[0])?$arguments[0]:"";
        if(!is_string($arguments[0])){
            $msg = print_r($arguments[0],true);
        }

        $data = self::preData();
        $data[]=$msg;
        self::outPut($name,$data);
    }

}