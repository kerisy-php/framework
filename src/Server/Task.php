<?php
/**
 *  task worker 处理
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Kerisy\Server;

use Kerisy\Core\Exception;
use Kerisy\Server\Swoole;

class Task
{
    public $server = null;
    public  $retryCount = 3;
    public  $logPath = "/tmp/taskFail.log";
    private static $numbersTmp = [];
    protected static $taskConfig = [];
    public  $timeOut = 1;
    protected static $config;

    public static function setConfig($config)
    {
        self::$config = $config;
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
        $serv = Swoole::$swooleServer;

        if (!$serv) {
            throw new Exception(" swoole server is not get");
        }

        if(self::$config){
            $this->retryCount = self::$config['task_retry_count'];
            $this->logPath = self::$config['task_fail_log'];
            $this->timeOut = self::$config['task_timeout'];
        }

        $this->server = $serv;
    }

    public function send($taskName, $params = [], $retryNumber = 0, $dstWorkerId = -1)
    {
        $sendData = [$taskName, $params, $retryNumber, $dstWorkerId];
        $this->server->task($sendData, $dstWorkerId);
    }

    public function finish($data)
    {
        list($status, $returnData, $exception) = $data;

        //如果执行不成功,进行重试
        if (!$status) {
            if ($returnData[2] < $this->retryCount) {
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
        swoole_async_write($this->logPath, $msg);
    }

    public function start($data)
    {
        list($task, $params) = $data;
        if (is_string($task)) {
            $task = strtolower($task);
            $taskClass = isset(self::$taskConfig[$task]) ? self::$taskConfig[$task] : null;
            if (!$taskClass) {
                throw new Exception(" task not config ");
            }

            $obj = new $taskClass();

            if (!method_exists($obj, "perform")) {
                throw new Exception(" task method perform not config ");
            }
            $result = call_user_func_array([$obj, "perform"], $params);
            return [true, $result, ''];
        }
        return [true, "", ''];
    }


    public static function __callStatic($name, $arguments)
    {
        $obj = new self();
        $dstWorkerId = $obj->getDstWorkerId();
        $obj->send($name, $arguments, $obj->retryCount,$dstWorkerId);
    }

    /**
     * 获取进程对应关系
     * @return mixed
     */
    protected function getDstWorkerId(){
        if(self::$numbersTmp){
            return array_pop(self::$numbersTmp);
        }else{
            $taskNumber = self::$config["task_worker_num"]-1;
            $start = 0;
            $end = $taskNumber;
            $numbers = range($start, $end);
            //按照顺序执行,保证每个连接处理数固定
            self::$numbersTmp = $numbers;
            return array_pop(self::$numbersTmp);
        }
    }

}