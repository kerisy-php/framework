<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/7/2
 */
namespace Kerisy\Job;
class Job extends JobBase{
    private static $dbPre;
    public function __construct($worker_name, $job_id, $options = array()) {
        $config = config('database')->get('job');
        self::$dbPre = $config['prefix'];
        
        $options = array_merge(array(
            "max_attempts" => 5,
            "fail_on_output" => false
        ), $options);
        $this->worker_name = $worker_name;
        $this->job_id = $job_id;
        $this->max_attempts = $options["max_attempts"];
        $this->fail_on_output = $options["fail_on_output"];
        $this->queue =  $options["queue"];
    }
    public function run() {
        # pull the handler from the db
        $r = $this->getHandler();
        $handler =unserialize($r["handler"]);

        if (!is_object($handler)) {
            $msg = "[JOB] bad handler for job::{$this->job_id}";
            $this->finishWithError($msg);
            return false;
        }
        # run the handler
        try {
            if ($this->fail_on_output) {
                ob_start();
            }
            $handler->perform();
            if ($this->fail_on_output) {
                $output = ob_get_contents();
                ob_end_clean();
                if (!empty($output)) {
                    throw new \Exception("Job produced unexpected output: $output");
                }
            }


            # cleanup
            $this->finish();
            $this->saveNext($r);
            return true;
        } catch (JobRetryException $e) {
            if ($this->fail_on_output) {
                ob_end_flush();
            }

            # attempts hasn't been incremented yet.
            $attempts = $this->getAttempts()+1;
            $msg = "Caught JobRetryException \"{$e->getMessage()}\" on attempt $attempts/{$this->max_attempts}.";
            if($attempts == $this->max_attempts) {
                $msg = "[JOB] job::{$this->job_id} $msg Giving up.";
                $this->finishWithError($msg, $handler);
            } else {
                $this->log("[JOB] job::{$this->job_id} $msg Try again in {$e->getDelay()} seconds.", self::WARN);
                $this->retryLater($e->getDelay());
            }
            return false;
        } catch (\Exception $e) {
            if ($this->fail_on_output) {
                ob_end_flush();
            }
            $this->finishWithError($e->getMessage(), $handler);
            return false;
        }
    }

    /**
     * 执行完成后插入下一次
     */
    public function saveNext($r){
        $handler =unserialize($r["handler"]);
        $schedule = $r['schedule'];

        if(!$schedule) return true;
        $cron = \Cron\CronExpression::factory($schedule);
        $run_at= $cron->getNextRunDate()->format('Y-m-d H:i:s');
        echo "run_at:".$run_at."\r\n";

        $config = config('database')->get('job');
        self::$dbPre = $config['prefix'];

        $now = date('Y-m-d H:i:s');
        $affected = self::runUpdate(
            "INSERT INTO " . self::$dbPre.self::$tableName . " (handler, queue, run_at, created_at,schedule) VALUES(?, ?, ?, '".$now."',?)",
            array(serialize($handler), (string) $this->queue, $run_at,$schedule)
        );
        if ($affected < 1) {
            self::log("[JOB] failed to enqueue new job", self::ERROR);
            return false;
        }
    }

    public function acquireLock() {
        $this->log("[JOB] attempting to acquire lock for job::{$this->job_id} on {$this->worker_name}", self::INFO);
        $now = date('Y-m-d H:i:s');
        $lock = $this->runUpdate("
            UPDATE " . self::$dbPre.self::$tableName . "
            SET    locked_at = '".$now."', locked_by = ?
            WHERE  id = ? AND (locked_at IS NULL OR locked_by = ?) AND failed_at IS NULL
        ", array($this->worker_name, $this->job_id, $this->worker_name));
        if (!$lock) {
            $this->log("[JOB] failed to acquire lock for job::{$this->job_id}", self::INFO);
            return false;
        }
        return true;
    }
    public function releaseLock() {
        $this->runUpdate("
            UPDATE " . self::$dbPre.self::$tableName . "
            SET locked_at = NULL, locked_by = NULL
            WHERE id = ?",
            array($this->job_id)
        );
    }
    public function finish() {
        $this->runUpdate(
            "DELETE FROM " . self::$dbPre.self::$tableName . " WHERE id = ?",
            array($this->job_id)
        );
        $this->log("[JOB] completed job::{$this->job_id}", self::INFO);
    }
    public function finishWithError($error, $handler = null) {
        $now = date('Y-m-d H:i:s');
        $this->runUpdate("
            UPDATE " . self::$dbPre.self::$tableName . "
            SET attempts = attempts + 1,
                failed_at = IF(attempts >= ?, '".$now."', NULL),
                error = IF(attempts >= ?, ?, NULL)
            WHERE id = ?",
            array(
                $this->max_attempts,
                $this->max_attempts,
                $error,
                $this->job_id
            )
        );
        $this->log($error, self::ERROR);
        $this->log("[JOB] failure in job::{$this->job_id}", self::ERROR);
        $this->releaseLock();
        if ($handler && ($this->getAttempts() == $this->max_attempts) && method_exists($handler, '_onDjjobRetryError')) {
            $handler->_onDjjobRetryError($error);
        }
    }
    public function retryLater($delay) {
        $now = date('Y-m-d H:i:s');
        $this->runUpdate("
            UPDATE " . self::$dbPre.self::$tableName . "
            SET run_at = DATE_ADD('".$now ."', INTERVAL ? SECOND),
                attempts = attempts + 1
            WHERE id = ?",
            array(
                $delay,
                $this->job_id
            )
        );
        $this->releaseLock();
    }
    public function getHandler() {
        $rs = $this->runQuery(
            "SELECT handler,schedule FROM " . self::$dbPre.self::$tableName . " WHERE id = ?",
            array($this->job_id)
        );
        return current($rs);
    }
    public function getAttempts() {
        $rs = $this->runQuery(
            "SELECT attempts FROM " . self::$dbPre.self::$tableName . " WHERE id = ?",
            array($this->job_id)
        );
        foreach ($rs as $r) return $r["attempts"];
        return false;
    }
    public static function enqueue($handler, $queue = "default", $run_at = null,$schedule=null) {
        $config = config('database')->get('job');
        self::$dbPre = $config['prefix'];
        $run_at = $run_at?$run_at:null;
        $queueConfig = config('job');
        $onlyOne=0;
        if($queueConfig){
            foreach ($queueConfig as $v) {
                if(($v['queue']==$queue)){
                    $onlyOne= $v['onlyOne'];
                    break;
                }
            }
        }
        if($onlyOne){
            $rs = self::runQuery(
                "SELECT id FROM " . self::$dbPre.self::$tableName . " WHERE queue = ?",
                array((string) $queue)
            );
            if($rs){
//                self::log("[JOB] cron job has exist, failed to enqueue new job", self::ERROR);
                return false;
            }
        }
        $now = date('Y-m-d H:i:s');
        $affected = self::runUpdate(
            "INSERT INTO " . self::$dbPre.self::$tableName . " (handler, queue, run_at, created_at,schedule) VALUES(?, ?, ?, '".$now."',?)",
            array(serialize($handler), (string) $queue, $run_at, $schedule)
        );
        if ($affected < 1) {
            self::log("[JOB] failed to enqueue new job", self::ERROR);
            return false;
        }
        return self::getConnection()->lastInsertId(); // return the job ID, for manipulation later
    }
    public static function bulkEnqueue($handlers, $queue = "default", $run_at = null,$schedule=null) {
        $config = config('database')->get('job');
        self::$dbPre = $config['prefix'];
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO " . self::$dbPre.self::$tableName . " (handler, queue, run_at, created_at,schedule) VALUES";
        $sql .= implode(",", array_fill(0, count($handlers), "(?, ?, ?, '".$now."',?)"));
        $parameters = array();
        foreach ($handlers as $handler) {
            $parameters []= serialize($handler);
            $parameters []= (string) $queue;
            $parameters []= $run_at;
            $parameters [] = $schedule;
        }
        $affected = self::runUpdate($sql, $parameters);
        echo $affected."ok\r\n";
        if ($affected < 1) {
            self::log("[JOB] failed to enqueue new jobs", self::ERROR);
            return false;
        }
        if ($affected != count($handlers))
            self::log("[JOB] failed to enqueue some new jobs", self::ERROR);
        return true;
    }
    public static function status($queue = "default") {
        $config = config('database')->get('job');
        self::$dbPre = $config['prefix'];

        $rs = self::runQuery("
            SELECT COUNT(*) as total, COUNT(failed_at) as failed, COUNT(locked_at) as locked
            FROM `" . self::$dbPre.self::$tableName . "`
            WHERE queue = ?
        ", array($queue));
        $rs = $rs[0];
        $failed = $rs["failed"];
        $locked = $rs["locked"];
        $total  = $rs["total"];
        $outstanding = $total - $locked - $failed;
        return array(
            "outstanding" => $outstanding,
            "locked" => $locked,
            "failed" => $failed,
            "total"  => $total
        );
    }
}