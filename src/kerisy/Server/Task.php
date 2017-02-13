<?php
/**
 *  task worker 处理
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

namespace Kerisy\Server;

use Kerisy\Server\Exception\InvalidArgumentException;
use Kerisy\Server\Facade\Context as FacedeContext;
use Kerisy\Coroutine\Event;
use Kerisy\Support\Arr;
use Kerisy\Support\ElapsedTime;
use Kerisy\Coroutine\Base\CoroutineTask;

class Task
{
    private $server = null;
    private  $retryCount = 2;
    private  $logPath = "/tmp/taskFail.log";
    private static $numbersTmp = [];
    protected static $taskConfig = [];
    protected  $timeOut = 3;
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
        $serv = FacedeContext::server();

        if (!$serv) {
            throw new InvalidArgumentException(" swoole server is not get");
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
        //执行数据清空event
        Event::fire("clear");
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
        $msg = date('Y-m-d H:i:s') . " " . Arr::my_json_encode($returnData);
        if ($exception) {
            $msg .= "\n================================================\n" .
                $exception .
                "\n================================================\n";
        }
        swoole_async_write($this->logPath, $msg);
    }

    public function start($data)
    {
        ElapsedTime::setStartTime(ElapsedTime::SYS_START);

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

            if ($result instanceof \Generator) {
                $task = new CoroutineTask($result);
                $task->work($task->getRoutine());
                unset($task);
            }
            return [true, $result, ''];
        }
        return [true, "", ''];
    }


    public function __call($name, $arguments)
    {
        $dstWorkerId = $this->getDstWorkerId();
        $this->send($name, $arguments, $this->retryCount,$dstWorkerId);
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
            //按照顺序执行,保证每个连接池子数固定
            self::$numbersTmp = $numbers;
            return array_pop(self::$numbersTmp);
        }
    }

}