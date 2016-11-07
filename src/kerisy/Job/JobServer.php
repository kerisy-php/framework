<?php
/**
 * job server
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

use Kerisy\Foundation\Storage\Redis;
use Kerisy\Server\ProcessServer;
use Kerisy\Support\Log;

class JobServer
{
    private $config = [];

    public function __construct(array $config, $root)
    {
        $this->config = $config;
    }

    /**
     * 清空所有job
     */
    public function clear()
    {
        $perform = $this->config['perform'];
        $storage = new Redis();
        foreach ($perform as $queueName => $v) {
            $key = Job::JOB_KEY_PRE . ":" . $queueName;
            $storage->del($key);
        }
    }

    /**
     * job 服务开始
     */
    public function start()
    {
        $name = isset($this->config['server']['name']) ? $this->config['server']['name'] : "kerisy";
        $preName = $name."-job";
        $serverName = $preName . "-master";
        swoole_set_process_name($serverName);
//        Log::sysinfo("[$serverName] start ...");
        //start job run
        $this->config['server_name'] = $name;
        $job = new Job($this->config);
        $perform = $this->config['perform'];

        $processServer = new ProcessServer($this->config['server'],true);
        $name = $preName . "-worker";

        foreach ($perform as $key => $v) {
            $processServer->add(
                function (\swoole_process $worker) use ($key, $job, $name) {
                    $worker->name($name);
                    Log::sysinfo("[$name] start ...");
                    $job->start($key);
                }
            );
        }
    }
}