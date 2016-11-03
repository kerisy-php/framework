<?php
/**
 *  task worker 处理
 *
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 上午11:10
 */

namespace Kerisy\Server;

use Kerisy\Server\Exception\InvalidArgumentException;
use Kerisy\Server\Facade\Context as FacedeContext;
use Kerisy\Coroutine\Event;

class Task
{
    private $server = null;
    private static $retryCount = 2;
    private static $logPath = "/tmp/taskFail.log";
    protected static $taskConfig = [];
    protected static $timeOut = 3;


    public static function setTimeOut($timeOut)
    {
        self::$timeOut = $timeOut;
    }

    public static function getTimeOut()
    {
        return self::$timeOut;
    }

    public static function getLogPath()
    {
        return self::$logPath;
    }

    public static function setLogPath($logPath)
    {
        self::$logPath = $logPath;
    }

    public static function setRetryCount($retryCount)
    {
        self::$retryCount = $retryCount;
    }

    public static function getRetryCount()
    {
        return self::$retryCount;
    }

    public static function setTaskConfig($taskConfig)
    {
        self::$taskConfig = $taskConfig;
    }

    public static function getTaskConfig()
    {
        return self::$taskConfig;
    }

    public function __construct()
    {
        $serv = FacedeContext::server();

        if (!$serv) {
            throw new InvalidArgumentException(" swoole server is not get");
        }

        $this->server = $serv;
    }

    public function send($taskName, $params = [], $retryNumber = 0, $dstWorkerId = -1)
    {
        $sendData = [$taskName, $params, $retryNumber, $dstWorkerId];
        $this->server->task($sendData, $dstWorkerId);
        //执行数据清空event
        Event::fire("clear");
    }

    public function finish($data)
    {
        list($status, $returnData, $exception) = $data;

        //如果执行不成功,进行重试
        if (!$status) {
            if ($returnData[2] < self::$retryCount) {
                //重试次数加1
                list($taskName, $params, $retryNumber, $dstWorkerId) = $returnData;
                $retryNumber = $retryNumber + 1;
                $this->send($taskName, $params, $retryNumber, $dstWorkerId);
            } else {
                $this->log($exception, $returnData);
            }
        }
    }


    private function log($exception, $returnData)
    {
        //超过次数,记录日志
        $msg = date('Y-m-d H:i:s') . " " . json_encode($returnData);
        if ($exception) {
            $msg .= "\n================================================\n" .
                $exception .
                "\n================================================\n";
        }
        swoole_async_write(self::$logPath, $msg);
    }

    public function start($data)
    {
        list($task, $params) = $data;
        if (is_string($task)) {
            $taskClass = isset(self::$taskConfig[$task]) ? self::$taskConfig[$task] : null;
            if (!$taskClass) {
                throw new InvalidArgumentException(" task not config ");
            }

            $obj = new $taskClass();

            if (!method_exists($obj, "perform")) {
                throw new InvalidArgumentException(" task method perform not config ");
            }
            $result = call_user_func_array([$obj, "perform"], $params);
            return [true, $result, ''];
        }
        return [true, "", ''];
    }

    public function __call($name, $arguments)
    {
        $this->send($name, $arguments);
    }

    public function __destruct()
    {
        self::$taskConfig = [];
    }

}