<?php
namespace Trendi\Coroutine;

use Trendi\Support\Log;

class Scheduler
{
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;
    // resourceID => [socket, tasks]
    protected $waitingForRead = [];
    protected $waitingForWrite = [];

    public function __construct()
    {
        $this->taskQueue = new \SplQueue();
    }

    public function newTask(\Generator $coroutine)
    {
        if($this->maxTaskId >=PHP_INT_MAX){
            $tid = 1;
        }else{
            $tid = ++$this->maxTaskId;
        }
        
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task)
    {
        $this->taskQueue->enqueue($task);
    }

    public function run()
    {
        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();
            Log::info(date('Y-m-d H:i:s'). "-taskId:".$task->getTaskId());
            try {
                $retval = $task->run();
                if ($retval instanceof SystemCall) {
                    $retval($task, $this);
                    continue;
                }
            } catch (\Exception $e) {
                $task->setException($e);
                $this->schedule($task);
            } catch (\Error $e) {
                $task->setException($e);
                $this->schedule($task);
            }
            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }

    public function killTask($tid)
    {
        if (!isset($this->taskMap[$tid])) {
            return false;
        }
        unset($this->taskMap[$tid]);

        // This is a bit ugly and could be optimized so it does not have to walk the queue,
        // but assuming that killing tasks is rather rare I won't bother with it now
        foreach ($this->taskQueue as $i => $task) {
            if ($task->getTaskId() === $tid) {
                unset($this->taskQueue[$i]);
                break;
            }
        }
        return true;
    }
}