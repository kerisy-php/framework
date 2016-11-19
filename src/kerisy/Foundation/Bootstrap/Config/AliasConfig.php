<?php
/**
 *  di setting
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Bootstrap\Config;


class AliasConfig
{

    public static function getOptions()
    {
        return [
            "Di" => \Kerisy\Di\Di::class,
            "RunMode" => \Kerisy\Support\RunMode::class,
            "Arr" => \Kerisy\Support\Arr::class,
            "Dir" => \Kerisy\Support\Dir::class,
            "Helper" => \Kerisy\Support\Helper::class,
            "Config" => \Kerisy\Config\Config::class,
            "Route" => \Kerisy\Mvc\Route\Route::class,
            "Context" => \Kerisy\Server\Facade\Context::class,
            "Task" => \Kerisy\Server\Facade\Task::class,
            "Job" => \Kerisy\Foundation\Bootstrap\Facade\Job::class,
            "Log" => \Kerisy\Foundation\Bootstrap\Facade\Log::class,
            "Session" => \Kerisy\Foundation\Bootstrap\Facade\Session::class,
            "Controller"=>\Kerisy\Foundation\Controller::class,
            "RpcController"=>\Kerisy\Rpc\Controller::class,
            "Lang"=>\Kerisy\Support\Lang::class,
        ];
    }

}