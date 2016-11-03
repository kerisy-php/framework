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