<?php
/**
 * 连接池任务
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
use Kerisy\Server\Facade\Context as FacedeContext;

class Pool
{
    protected $config = null;
    private $timeOut = 10;
    private $poolWorkrNumberConfig = [];
    private static $numbersTmp = [];
    public static $poolTaskData=[];
    
    public function __construct($config)
    {
        $this->config = $config;
        $this->timeOut = isset($config['task_timeout']) ? $config['task_timeout'] : $this->timeOut;
        $poolWorkrNumber = 0;
        $poolWorkrNumberConfig = $config['pool_worker_number'];
        if ($poolWorkrNumberConfig) {
            foreach ($poolWorkrNumberConfig as $v) {
                $poolWorkrNumber += $v;
            }
        }
        $this->poolWorkrNumberConfig = $poolWorkrNumberConfig;
    }

    /**
     *  获取数据
     *
     * @param $taskName
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function get($taskName, $params = [])
    {
        $workerId = posix_getpid();
        if($taskName == 'pdo'){
            $dstWorkerId = $this->getDstWorkerId($taskName, $workerId);
        }else{
            $dstWorkerId = $this->getDstWorkerIdNotDb($taskName);
        }
        $serv = FacedeContext::server();
        $sendData = [$taskName, $params];
        $result = $serv->taskwait($sendData, $this->timeOut, $dstWorkerId);
        list($status, $returnData, $exception) = $result;
        if ($status !== false) {
            return $returnData;
        } else {
            $this->log($exception, $returnData);
            return null;
        }
    }

    /**
     * 不同连接池,分配不同task worker
     *
     * @param $taskName
     * @return int
     */
    protected function getDstWorkerId($taskName, $workerId)
    {
        $taskName = strtolower($taskName);
        if (!isset($this->poolWorkrNumberConfig[$taskName])) return -1;
        if(isset(self::$poolTaskData[$workerId])){
            return self::$poolTaskData[$workerId];
        }else{
            if(isset(self::$numbersTmp[$taskName]) && self::$numbersTmp[$taskName]){
                return self::$poolTaskData[$workerId] = array_pop(self::$numbersTmp[$taskName]);
            }else{
                $pre = 0;
                $current = 0;
                foreach ($this->poolWorkrNumberConfig as $k => $v) {
                    if ($k == $taskName) {
                        $current = $v;
                        break;
                    }
                    $pre = $v;
                }
                $start = $pre;
                $end = $pre + $current - 1;
                $numbers = range($start, $end);
                //按照顺序执行,保证每个连接池子数固定
                self::$numbersTmp[$taskName] = $numbers;
                
                self::$poolTaskData[$workerId] = array_pop(self::$numbersTmp[$taskName]);
                return self::$poolTaskData[$workerId];
            }
        }
    }

    /**
     * 不同连接池,分配不同task worker
     *
     * @param $taskName
     * @return int
     */
    protected function getDstWorkerIdNotDb($taskName)
    {
        $taskName = strtolower($taskName);
        if (!isset($this->poolWorkrNumberConfig[$taskName])) return -1;
        if(isset(self::$numbersTmp[$taskName]) && self::$numbersTmp[$taskName]){
            return array_pop(self::$numbersTmp[$taskName]);
        }else{
            $pre = 0;
            $current = 0;
            foreach ($this->poolWorkrNumberConfig as $k => $v) {
                if ($k == $taskName) {
                    $current = $v;
                    break;
                }
                $pre = $v;
            }
            $start = $pre;
            $end = $pre + $current - 1;
            $numbers = range($start, $end);
            //按照顺序执行,保证每个连接池子数固定
            self::$numbersTmp[$taskName] = $numbers;

            return array_pop(self::$numbersTmp[$taskName]);
        }
    }

    /**
     * 保存失败执行记录
     *
     * @param $exception
     * @param $returnData
     */
    private function log($exception, $returnData)
    {
        //超过次数,记录日志
        $msg = date('Y-m-d H:i:s') . " " . json_encode($returnData);
        if ($exception) {
            $msg .= "\n================================================\n" .
                $exception .
                "\n================================================\n";
        }
        swoole_async_write($this->config['task_fail_log'], $msg);
    }
}