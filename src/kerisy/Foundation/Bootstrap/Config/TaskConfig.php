<?php
/**
 *  di setting
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午6:08
 */

namespace Kerisy\Foundation\Bootstrap\Config;


class TaskConfig
{

    public static function getOptions()
    {
        return [
            "email" => \Kerisy\Foundation\Bootstrap\Task\Email::class,
            "pdo" => \Kerisy\Pool\Task\Pdo::class,
            "redis" => \Kerisy\Pool\Task\Redis::class,
        ];
    }

}