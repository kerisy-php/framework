<?php
/**
 *  exception 格式化
 *
 * User: Peter Wang
 * Date: 16/9/15
 * Time: 下午9:18
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
        $trace = array_slice($trace,0,7);
        $message .= implode("\n", $trace);
        return $message;
    }

}