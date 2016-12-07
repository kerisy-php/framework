<?php
/**
 *  exception 格式化
 *
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Support;


class Exception
{

    /**
     *  格式化 eception
     *
     * @param $e
     * @return string
     */
    public static function formatException($e)
    {
        $message = "Exception Error : " . $e->getMessage();
        $message .= " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $message .= "Stack trace\n";

        $trace = explode("\n", $e->getTraceAsString());
//        $trace = array_slice($trace,0,7);
        $message .= implode("\n", $trace);
        return $message;
    }

}