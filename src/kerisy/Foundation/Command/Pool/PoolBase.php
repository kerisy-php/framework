<?php
/**
 * User: Peter Wang
 * Date: 16/9/15
 * Time: 下午10:19
 */

namespace Kerisy\Foundation\Command\Pool;

use Kerisy\Config\Config;
use Kerisy\Pool\PoolServer;
use Kerisy\Support\Arr;
use Kerisy\Support\Dir;
use Kerisy\Support\Serialization\Serialization;
use Kerisy\Support\ElapsedTime;
use Kerisy\Support\Log;

class PoolBase
{
    public static function operate($cmd, $output, $input)
    {
        ElapsedTime::setStartTime(ElapsedTime::SYS_START);
        $root = Dir::formatPath(ROOT_PATH);
        $config = Config::get("server.pool");
        $appName = Config::get("server.name");

        if (!$appName) {
            Log::sysinfo("server.name not config");
            exit(0);
        }

        if (!$config) {
            Log::sysinfo("pool config not config");
            exit(0);
        }

        if (!isset($config['server'])) {
            Log::sysinfo("pool.server config not config");
            exit(0);
        }

        if (!isset($config['server']['pool_worker_number'])) {
            Log::sysinfo("pool.server.pool_worker_number config not config");
            exit(0);
        }

        if ($input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $config['server']['daemonize'] = $daemonize == 0 ? 0 : 1;
        }

        if (!isset($config['server']['host'])) {
            Log::sysinfo("pool.server.host config not config");
            exit(0);
        }

        if (!isset($config['server']['port'])) {
            Log::sysinfo("pool.server.port config not config");
            exit(0);
        }
        self::doOperate($cmd, $config, $root, $appName, $output);
    }


    public static function doOperate($command, array $config, $root, $appName, $output)
    {
        $defaultConfig = [
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            'worker_num' => 2,
            //reactor数量，推荐2
            'reactor_num' => 2,
            "mem_reboot_rate" => 0,
            "dispatch_mode" => 2,
            'static_path' => $root . '/public',
            "gzip" => 4,
            "static_expire_time" => 86400,
            "task_worker_num" => 5,
            "task_fail_log" => "/tmp/task_fail_log",
            "task_retry_count" => 2,
            "serialization" => 1,
            //以下配置直接复制，无需改动
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 2000000,
            "pid_file" => "/tmp/pid",
        ];

        $config['server'] = Arr::merge($defaultConfig, $config['server']);

        if(isset($config['server']['log_file']) && !is_file($config['server']['log_file'])){
            mkdir(dirname($config['server']['log_file']), "0777", true);
        }
        
        $serverName = $appName . "-pool-master";
        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $masterPidArr);
        $masterPid = $masterPidArr ? current($masterPidArr) : null;

        if ($command === 'start' && $masterPid) {
            Log::sysinfo("[$serverName] already running");
            return;
        }

        if ($command !== 'start' && $command !== 'restart' && !$masterPid) {
            Log::sysinfo("[$serverName] not run");
            return;
        }
        // execute command.
        switch ($command) {
            case 'status':
                if ($masterPid) {
                    Log::sysinfo("[$serverName] already running");
                } else {
                    Log::sysinfo("[$serverName] not run");
                }
                break;
            case 'start':
                self::start($config, $root, $appName);
                break;
            case 'stop':
                self::stop($appName);
                Log::sysinfo("[$serverName] stop success ");
                break;
            case 'restart':
                self::stop($appName);
                self::start($config, $root, $appName);
                break;
            default :
                return "";
        }
    }

    protected static function stop($appName)
    {
        $killStr = $appName . "-pool";
        exec("ps axu|grep " . $killStr . "|awk '{print $2}'|xargs kill -9", $masterPidArr);
    }

    protected static function start($config, $root, $appName)
    {
        $swooleServer = new \swoole_server($config['server']['host'], $config['server']['port']);
        $serialization = Serialization::get($config['server']['serialization']);
        $serialization->setBodyOffset($config['server']['package_body_offset']);
        $obj = new PoolServer($swooleServer, $serialization, $config, $root, $appName);
        $obj->start();
    }

}