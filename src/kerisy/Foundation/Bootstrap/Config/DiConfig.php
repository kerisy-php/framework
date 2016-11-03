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