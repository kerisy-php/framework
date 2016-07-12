<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/7/2
 */

namespace Kerisy\Job;


class Worker extends JobBase
{
   static $dbPre = null;

    public function __construct($options = array()) {
        $config = config('database')->get('job');
        self::$dbPre = $config['prefix'];
        
        $options = array_merge(array(
            "queue" => "default",
            "count" => 0,
            "sleep" => 5,
            "max_attempts" => 5,
            "fail_on_output" => false
        ), $options);
        list($this->queue, $this->count, $this->sleep, $this->max_attempts, $this->fail_on_output) =
            array($options["queue"], $options["count"], $options["sleep"], $options["max_attempts"], $options["fail_on_output"]);
        list($hostname, $pid) = array(trim(gethostname()), getmypid());
        $this->name = "host::$hostname pid::$pid";
        if (function_exists("pcntl_signal")) {
            pcntl_signal(SIGTERM, array($this, "handleSignal"));
            pcntl_signal(SIGINT, array($this, "handleSignal"));
        }
    }
    public function handleSignal($signo) {
        $signals = array(
            SIGTERM => "SIGTERM",
            SIGINT  => "SIGINT"
        );
        $signal = $signals[$signo];
        $this->log("[WORKER] Received received {$signal}... Shutting down", self::INFO);
        $this->releaseLocks();
        exit(0);
//        \Tr\Helper::myexit(0);
    }
    public function releaseLocks() {

        $this->runUpdate("
            UPDATE " . self::$dbPre.self::$tableName . "
            SET locked_at = NULL, locked_by = NULL
            WHERE locked_by = ?",
            array($this->name)
        );
    }
    /**
     * Returns a new job ordered by most recent first
     * why this?
     *     run newest first, some jobs get left behind
     *     run oldest first, all jobs get left behind
     * @return DJJob
     */
    public function getNewJob() {
        # we can grab a locked job if we own the lock
        $now = date('Y-m-d H:i:s');
        $rs = $this->runQuery("
            SELECT id
            FROM   " . self::$dbPre.self::$tableName . "
            WHERE  queue = ?
            AND    (run_at IS NULL OR '".$now."' >= run_at)
            AND    (locked_at IS NULL OR locked_by = ?)
            AND    failed_at IS NULL
            AND    attempts < ?
            ORDER BY created_at DESC
            LIMIT  10
        ", array($this->queue, $this->name, $this->max_attempts));
        // randomly order the 10 to prevent lock contention among workers
        shuffle($rs);
        foreach ($rs as $r) {
            $job = new Job($this->name, $r["id"], array(
                "max_attempts" => $this->max_attempts,
                "fail_on_output" => $this->fail_on_output,
                "queue"=>$this->queue,
            ));
            if ($job->acquireLock()) return $job;
        }
        return false;
    }
    public function start() {
        $this->log("[JOB] Starting worker {$this->name} on queue::{$this->queue}", self::INFO);
        try {
            while (1) {
                if (function_exists("pcntl_signal_dispatch")) pcntl_signal_dispatch();
                $job = $this->getNewJob();
                if (!$job) {
                    $this->log("[JOB] Failed to get a job, queue::{$this->queue} may be empty", self::DEBUG);
                    sleep($this->sleep);
                    continue;
                }
                $job->run();
            }
        } catch (\Exception $e) {
            $this->log("[JOB] unhandled exception::\"{$e->getMessage()}\"", self::ERROR);
        }
    }
    
    static public function run($options){
        $queueConfig = config('job');
        if(!$queueConfig) return false;

        $obj = new \Kerisy\Job\Process(count($queueConfig));
        $obj->workerStart=function(\swoole_process $worker)use($queueConfig){
            if(!$queueConfig) return false;
            $worker->name("job server worker #".$worker->id);
            $v = $queueConfig[$worker->id];
            $worker = new self($v);
            $worker->start();
        };
        $obj->run($options);
    }
}