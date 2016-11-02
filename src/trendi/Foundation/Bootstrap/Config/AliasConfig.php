<?php
/**
 *  di setting
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午6:08
 */

namespace Trendi\Foundation\Bootstrap\Config;


class AliasConfig
{

    public static function getOptions()
    {
        return [
            "Di" => \Trendi\Di\Di::class,
            "RunMode" => \Trendi\Support\RunMode::class,
            "Arr" => \Trendi\Support\Arr::class,
            "Dir" => \Trendi\Support\Dir::class,
            "Helper" => \Trendi\Support\Helper::class,
            "Config" => \Trendi\Config\Config::class,
            "Route" => \Trendi\Mvc\Route\Route::class,
            "Context" => \Trendi\Server\Facade\Context::class,
            "Task" => \Trendi\Server\Facade\Task::class,
            "Job" => \Trendi\Foundation\Bootstrap\Facade\Job::class,
            "Log" => \Trendi\Foundation\Bootstrap\Facade\Log::class,
            "Session" => \Trendi\Foundation\Bootstrap\Facade\Session::class,
        ];
    }

}