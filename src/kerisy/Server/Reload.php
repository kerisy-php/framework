<?php
/**
 * 根据内存使用比率自动重启服务器
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

namespace Kerisy\Server;

use Kerisy\Support\Log;
use Kerisy\Config\Config;

class Reload
{

    public static function load($serverName, $rate, $config, $showLog=false)
    {
        $configApp = Config::get("app");
        if (isset($configApp['memory_limit'])) {
            ini_set('memory_limit', $configApp['memory_limit']);
        }

        $process = new \swoole_process(function(\swoole_process $worker) use($serverName, $rate, $config,$showLog){
            $worker->name($config['server_name']."-processCheck");
            swoole_timer_tick(1000, function()use($serverName, $rate, $config,$showLog){
                self::perform($serverName, $rate, $config, $showLog);
            });
        });
        $process->start();
    }
    
    public static function perform($serverName, $rate, $config, $showLog=false)
    {
        self::start($serverName, $rate, $showLog);
//        Log::info(($config['auto_reload']));
        if(isset($config['auto_reload']) && $config['auto_reload']){
            if((date('H:i') == $config['auto_reload'])){
                self::reload($serverName);
                //休息70s
                sleep(70);
            }
        }
    }

    protected static function reload($serverName)
    {
        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $serverPidArr);
        $masterPid = $serverPidArr ? current($serverPidArr) : null;
        if ($masterPid) {
            posix_kill($masterPid, SIGUSR1);
        }
    }

    protected static function start($serverName, $rate, $showLog=false)
    {
        if(!$rate) return ;
        
        if (self::check($rate, $showLog)) {
            return;
        } else {
            Log::warn("Memory is full ,will restart!");
        }

        self::reload($serverName);
    }

    protected static function check($rate, $showLog)
    {
        $mem = self::getMemory();
        $memoryLimit = ini_get("memory_limit");

        if ($memoryLimit == '-1') return true;
        $memoryLimitUnmber = substr($memoryLimit, 0, -1);

        if (strtolower(substr($memoryLimit, -1)) == 'g') {
            $memoryLimit = $memoryLimitUnmber * 1024;
        } elseif (strtolower(substr($memoryLimit, -1)) == 't') {
            $memoryLimit = $memoryLimitUnmber * 1024 * 1024;
        } else {
            $memoryLimit = $memoryLimitUnmber;
        }
//        $showLog = 1;
        if($showLog) Log::sysinfo("Memory:" . $mem . "M/" . $memoryLimit*$rate."M/".$memoryLimit."M");
        return ($mem / $memoryLimit) > $rate ? false : true;
    }


    public static function getMemory()
    {
        return round(memory_get_usage() / 1024 / 1024, 2);
    }


}