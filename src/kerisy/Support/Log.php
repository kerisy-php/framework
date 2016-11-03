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
    private static $instance = null;

    /**
     * 颜色初始化
     *
     * @param $foreground_colors
     * @param $background_colors
     */
    protected function init()
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

    /**
     * Returns colored string
     * @param $string
     * @param null $foreground_color
     * @param null $background_color
     * @return string
     */
    protected function getColoredString($string, $foreground_color = null, $clean=0)
    {
        
        
        $foreground_colors = $this->init();
        $colored_string = "";
        $ip = swoole_get_local_ip();
        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");
        $preStr ="[". date('Y-m-d H:i:s')."][".posix_getpid()."][".current($ip)."][".$elapsedTime."ms]";
        // Check if given foreground color found
        if($clean){
            $preStr = "";
        }
        if (isset($foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $foreground_colors[$foreground_color] . "m".$preStr;
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";
        return $colored_string;
    }


    public static function __callStatic($name, $arguments)
    {
        if(!self::$instance){
            self::$instance = new self();
        }
        $pre = "[$name] ";

        if(!is_string($arguments[0])){
            $arguments[0] = print_r($arguments[0],true);
        }
        
        switch ($name) {
            case 'info':
                echo self::$instance->getColoredString($pre.$arguments[0], 'light_gray')."\n";
                break;
            case 'sysinfo':
                echo self::$instance->getColoredString($pre.$arguments[0], 'dark_gray')."\n";
                break;
            case 'warn':
                echo self::$instance->getColoredString($pre.$arguments[0], 'yellow')."\n";
                break;
            case 'debug':
                echo self::$instance->getColoredString($pre.$arguments[0], 'green')."\n";
                break;
            case 'show':
                echo self::$instance->getColoredString($arguments[0], 'green', true)."\n";
                break;
            case 'error':
                echo self::$instance->getColoredString($pre.$arguments[0], 'red')."\n";
                break;
        }
    }
}