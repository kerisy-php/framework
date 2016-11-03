<?php
/**
 * error exception handle
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

namespace Kerisy\Foundation\Bootstrap;

use Kerisy\Support\Exception;
use Kerisy\Support\Log;
use Kerisy\Coroutine\Event;

class ErrorHandleBootstrap
{

    protected static $instance = null;

    public static function getInstance($errorReportingLevel = E_ALL, $displayErrors = true)
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self($errorReportingLevel, $displayErrors);
    }

    public function __construct()
    {
        ini_set("swoole.display_errors", false);
        ini_set('display_errors', false);

        error_reporting(E_ALL ^ E_NOTICE);

        set_exception_handler([$this, 'handleException']);

        set_error_handler([$this, 'handleError']);

        register_shutdown_function([$this, 'handleShutdown']);

    }

    /**
     *  error handle
     *
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        restore_error_handler();
        $message = "WARNING  with message '{$message}' in " . $file . ':' . $line . "\n";
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        if ($trace) {
            foreach ($trace as $v) {
                $class = isset($v['class']) ? $v['class'] : "";
                $type = isset($v['type']) ? $v['type'] : "";
                $function = isset($v['function']) ? $v['function'] : "";
                $message .= $v['file'] . "(" . $v['line'] . "): " . $class . $type . $function . "\n";
            }
        }
        Log::warn($message);
    }

    /**
     * exception handle
     *
     * @param $e
     */
    public function handleException($e)
    {
        restore_exception_handler();
        Log::error(Exception::formatException($e));
    }

    /**
     * register_shutdown_function
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if (isset($error['type'])) {
            switch ($error['type']) {
                case E_ERROR :
                case E_PARSE :
                case E_CORE_ERROR :
                case E_COMPILE_ERROR :
                    $message = $error['message'];
                    $file = $error['file'];
                    $line = $error['line'];
                    $log = "$message ($file:$line)\nStack trace:\n";
                    $trace = debug_backtrace();
                    foreach ($trace as $i => $t) {
                        if (!isset($t['file'])) {
                            $t['file'] = 'unknown';
                        }
                        if (!isset($t['line'])) {
                            $t['line'] = 0;
                        }
                        if (!isset($t['function'])) {
                            $t['function'] = 'unknown';
                        }
                        $log .= "#$i {$t['file']}({$t['line']}): ";
                        if (isset($t['object']) and is_object($t['object'])) {
                            $log .= get_class($t['object']) . '->';
                        }
                        $log .= "{$t['function']}()\n";
                    }
                Log::error($log);
                default:
                    break;
            }
        }
    }

    public function __destruct()
    {
    }
}