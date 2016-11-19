<?php
/**
 *  job处理
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

namespace Kerisy\Job;

use Kerisy\Job\Cron\CronExpression;
use Kerisy\Foundation\Storage\Redis;
use Kerisy\Job\Exception\InvalidArgumentException;
use Kerisy\Server\Reload;
use Kerisy\Support\Exception;
use Kerisy\Support\Log;
use Kerisy\Support\Exception\RuntimeExitException;
use Kerisy\Coroutine\Base\CoroutineTask;

class Job
{

    const JOB_KEY_PRE = "JOB_KEY";

    private $config = [];

    /**
     * @var \Kerisy\Foundation\Storage\Redis
     */
    private $storage = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->storage = new Redis();
    }

    /**
     * job 服务开始
     * @param $queueName
     */
    public function start($queueName)
    {
        if (!$this->config) return;

        $timeTick = isset($this->config['server']['timer_tick']) ? $this->config['server']['timer_tick'] : 500;
        $this->config['auto_reload'] = isset($this->config['server']['auto_reload'])?$this->config['server']['auto_reload']:false;
        \swoole_timer_tick($timeTick, function () use ($queueName) {
            $this->run($queueName);
            Reload::perform($this->config['server_name'] . "-master", $this->config['server']['mem_reboot_rate'], $this->config);
        });
    }

    /**
     * job 服务执行
     * @param $queueName
     */
    private function run($queueName)
    {
        try {
            if (!isset($this->config['perform'][$queueName])) return;
            $pv = $this->config['perform'][$queueName];
            $key = self::JOB_KEY_PRE . ":" . $queueName;
            $now = time();
            $data = $this->storage->zrangebyscore($key, 0, $now);
            //原子操作避免重复处理
            $checkKey = self::JOB_KEY_PRE . "CHECK";
            $check = $this->storage->setnx($checkKey, 1);
            if (!$check) {
                $sleep = $pv['sleep'] ? $pv['sleep'] : 1;
                sleep($sleep);
            }
            if ($data && is_array($data)) {
                foreach ($data as $v) {
                    list(, $value) = explode("@", $v);
                    $valueArr = unserialize($value);
                    $queueName = isset($valueArr[0]) ? $valueArr[0] : "";
                    $jobObj = isset($valueArr[1]) ? $valueArr[1] : "";
                    $schedule = isset($valueArr[3]) ? $valueArr[3] : "";
                    $tag = isset($valueArr[4]) ? $valueArr[4] : "";
                    if(!is_object($jobObj)){
                        continue;
                    }
                    $result = $jobObj->perform();
                    if ($result instanceof \Generator) {
                        $task = new CoroutineTask($result);
                        $task->work($task->getRoutine());
                        unset($task);
                    }
                    $this->storage->zrem($key, $v);
                    if ($schedule) {
                        $cron = CronExpression::factory($schedule);
                        $runTime = $cron->getNextRunDate()->format('Y-m-d H:i:s');
                        $this->add($queueName, $jobObj, $runTime, $schedule, $tag);
                    }
                }
            }
            $this->storage->del($checkKey);
            $sleep = $pv['sleep'] ? $pv['sleep'] : 1;
            sleep($sleep);
        } catch (RuntimeExitException $e){
            Log::syslog("RuntimeExitException:".$e->getMessage());
        }catch (\Exception $e) {
            Log::error("Job ERROR : \n" . Exception::formatException($e));
        } catch (\Error $e) {
            Log::error("Job ERROR : \n" . Exception::formatException($e));
        }
    }


    /**
     * 添加job
     * @param $queueName
     * @param $jobObj
     * @param string $runTime
     * @param string $schedule
     * @param string $tag
     * @throws InvalidArgumentException
     */
    public function add($queueName, $jobObj, $runTime = "", $schedule = "", $tag = "")
    {
        if (!isset($this->config['perform'][$queueName])) return;
        $key = self::JOB_KEY_PRE . ":" . $queueName;

        $config = $this->config['perform'][$queueName];

        if ($config['only_one']) {
            $data = $this->storage->zrange($key, 0, -1);
//            dump("--------------------job.total-------------------------");
//            dump($data);
            if ($data) return;
        }

        $value = func_get_args();
        if (!$tag) {
            $tag = md5(serialize($value));
        } else {
            if (stristr('@', $tag)) {
                throw new InvalidArgumentException("tag can not include '@'");
            }
        }

        if (!$runTime && !$schedule) {
            $runTime = time();
        } else {
            if (!$runTime) {
                $cron = CronExpression::factory($schedule);
                $runTime = $cron->getNextRunDate()->format('Y-m-d H:i:s');
            }
        }

        $runTime = is_string($runTime) ? strtotime($runTime) : $runTime;

        $value = [];
        $value[0] = $queueName;
        $value[1] = $jobObj;
        $value[2] = $runTime;
        $value[3] = $schedule;
        $value[4] = $tag;

        $saveVale = $tag . "@" . serialize($value);

        $this->storage->zadd($key, $runTime, $saveVale);
    }

}