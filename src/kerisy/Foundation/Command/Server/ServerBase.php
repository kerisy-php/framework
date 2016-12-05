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

namespace Kerisy\Foundation\Command\Server;

use Kerisy\Config\Config;
use Kerisy\Support\Log;
use Kerisy\Support\PhpExecutableFinder;


class ServerBase
{
    protected static $cmdHelp = null;

    public static function operate($cmd, $cmdObj, $input)
    {
        $options = [];
        if (($cmd == 'start' || $cmd == 'restart') && $input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            if ($daemonize) $options["daemonize"] = "-d";
        }

        if ($input->hasOption("option")) {
            $option = $input->getOption('option');
            if ($option) $options["option"] = $option;
        }

        $config = Config::get("server");
        if (!$config) {
            Log::sysinfo("server config not config");
            return;
        }

        $str = '
██╗  ██╗███████╗██████╗ ██╗███████╗██╗   ██╗
██║ ██╔╝██╔════╝██╔══██╗██║██╔════╝╚██╗ ██╔╝
█████╔╝ █████╗  ██████╔╝██║███████╗ ╚████╔╝ 
██╔═██╗ ██╔══╝  ██╔══██╗██║╚════██║  ╚██╔╝  
██║  ██╗███████╗██║  ██║██║███████║   ██║   
╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝╚═╝╚══════╝   ╚═╝                                                    
        ';
        Log::show($str);
        self::doOperate($cmd, $options, $config, $cmdObj);
        sleep(1);
        exit(0);
    }


    public static function doOperate($command, $options, array $config, $cmdObj)
    {
        self::runCmd($command, $config, $options, $cmdObj);
        $daemonizeStr = array_isset($options, "daemonize");
        if ($daemonizeStr) {
            \swoole_process::wait(false);
        } else {
            \swoole_process::wait();
        }

    }

    protected static function runCmd($type, $config, $options, $cmdObj)
    {
        $daemonizeStr = array_isset($options, "daemonize");
        $option = array_isset($options, "option");
        $runFileName = $_SERVER['SCRIPT_FILENAME'];
        $phpbin = self::getPhpBinary();
        $servers = $config['servers'];
        if ($servers) {
            foreach ($servers as $v) {
                $params = [$runFileName, $v . ":" . $type];
                if ($daemonizeStr) array_push($params, $daemonizeStr);
                if ($option) {
                    $optionTmp = explode(",", $option);
                    foreach ($optionTmp as $v) {
                        array_push($params, $v);
                    }
                }
                self::process($phpbin, $params, $cmdObj);
                self::check($config);
            }
        }
    }

    protected static function check($config)
    {
        $name = $config['name'];
        $count = -1;
        $time = time();
        while (1) {
            usleep(40000);
            exec("ps axu|grep " . $name . "|awk '{print $2}'", $masterArr);
            if ((time() - $time) > 30) {
                break;
            }
            if ($count == -1) {
                $count = count($masterArr);
                continue;
            } elseif (count($masterArr) == $count) {
                continue;
            }
            break;
        }
    }

    protected static function process($phpbin, $param, $cmdObj)
    {
        $process = new \swoole_process(function (\swoole_process $worker) use ($phpbin, $param, $cmdObj) {
            $param = self::getCmdHelp($param, $cmdObj);
            $worker->exec($phpbin, $param);
        }, false);
        $process->start();
    }

    protected static function getCmdHelp($param, $cmdObj)
    {
        $paramTmp = $param;
        $cmdName = array_isset($param, 1);
        if (!$cmdName) return;
        $obj = $cmdObj->getCmdDefinition($cmdName);
        $op = array_slice($param, 2);
        $tmp = [];
        foreach ($op as $v) {
            $shortName = substr(ltrim($v, "-"), 0, 1);
//            dump($shortName);
            $check = $obj->hasShortcut($shortName);
            if ($check) {
                $tmp[] = $v;
                continue;
            }

            $shortName = current(explode("=", ltrim($v, "-")));
//            dump($shortName);
            $check = $obj->hasOption($shortName);
            if ($check) {
                $tmp[] = $v;
            }
        }
        $newParam = array_slice($paramTmp, 0, 2);

        if ($tmp) $newParam = array_merge($newParam, $tmp);
//        dump($newParam);
        return $newParam;
    }

    protected static function getPhpBinary()
    {
        $executableFinder = new PhpExecutableFinder();

        return $executableFinder->find();
    }
}