<?php
/**
 *  di setting
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午6:08
 */

namespace Trendi\Foundation\Bootstrap\Config;


class TaskConfig
{

    public static function getOptions()
    {
        return [
            "email" => \Trendi\Foundation\Bootstrap\Task\Email::class,
            "pdo" => \Trendi\Pool\Task\Pdo::class,
            "redis" => \Trendi\Pool\Task\Redis::class,
        ];
    }

}