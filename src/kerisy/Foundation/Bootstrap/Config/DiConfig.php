<?php
/**
 *  di setting
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午6:08
 */

namespace Kerisy\Foundation\Bootstrap\Config;


class DiConfig
{

    public static function getOptions()
    {
        return [
            "context" => [
                "class" => \Kerisy\Server\Context::class
            ],
            "task" => [
                "class" => \Kerisy\Server\Task::class
            ],
            "job" => [
                "class" => \Kerisy\Foundation\Bootstrap\JobBootstrap::class
            ],
            "log" => [
                "class" => \Kerisy\Support\Log::class
            ],
            "session" => [
                "class" => \Kerisy\Foundation\Bootstrap\Session::class,
            ],
        ];
    }

}