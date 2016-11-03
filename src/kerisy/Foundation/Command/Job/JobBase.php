<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Command\Job;

use Kerisy\Config\Config;
use Kerisy\Job\JobServer;
use Kerisy\Support\Arr;
use Kerisy\Support\Dir;
use Kerisy\Support\ElapsedTime;
use Kerisy\Support\Log;

class JobBase
{
    public static function operate($cmd, $output, $input)
    {
        ElapsedTime::setStartTime(ElapsedTime::SYS_START);
        $root = Dir::formatPath(ROOT_PATH);
        $config = Config::get("server.job");
        $appName = Config::get("server.name");

        if (!$appName) {
            Log::sysinfo("server.name not config");
            exit(0);
        }

        if (!$config) {
            Log::sysinfo("job config not config");
            exit(0);
        }

        if (!isset($config['server'])) {
            Log::sysinfo("job.server config not config");
            exit(0);
        }


        if ($input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $config['server']['daemonize'] = $daemonize == 0 ? 0 : 1;
        }

        self::doOperate($cmd, $config, $root, $appName, $output);
    }


    public static function doOperate($command, array $config, $root, $appName, $output)
    {
        $defaultConfig = [
            //是否后台运行, 推荐设置0
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            'worker_num' => 2,
            "mem_reboot_rate" => 0,//可用内存达到多少自动重启
            "serialization" => 1
        ];

        $config['server'] = Arr::merge($defaultConfig, $config['server']);
        $config['server']['name'] = $appName;

        $serverName = $appName . "-job-master";
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
            case 'clear':
                $jobServer = new JobServer($config, $root);
                $jobServer->clear();
                break;
            case 'start':
                self::start($config, $root);
                break;
            case 'stop':
                self::stop($appName);
                Log::sysinfo("[$serverName] stop success ");
                break;
            case 'restart':
                self::stop($appName);
               self::start($config, $root);
                break;
            default :
                exit(0);
        }
    }

    protected static function stop($appName)
    {
        $killStr = $appName . "-job";
        exec("ps axu|grep " . $killStr . "|awk '{print $2}'|xargs kill -9", $masterPidArr);
    }

    protected static function start($config, $root)
    {
        $jobServer = new JobServer($config, $root);
        $jobServer->start();
    }

}