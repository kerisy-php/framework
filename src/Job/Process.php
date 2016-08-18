<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/7/8
 */
namespace Kerisy\Job;

class Process{
    public $workerStart = null;
    public $worker_num = 4;

    function __construct($worker_num,$redirect_stdout=false)
    {
        $this->worker_num = $worker_num;
        $this->redirect_stdout = $redirect_stdout;
    }


    function run($options){
        $asDaemon = isset($options['asDaemon'])?$options['asDaemon']:0;
        if($asDaemon){
            \swoole_process::daemon();
        }

        $pids = [];
        $workers = [];
        for($i = 0; $i < $this->worker_num; $i++)
        {
            $process = new \swoole_process($this->workerStart, $this->redirect_stdout);
            $process->id = $i;
            $pid = $process->start();
            $pids[]=$pid;
            $workers[$pid] = $process;
        }


        $pidFile = isset($options['pidFile'])?$options['pidFile']:0;
        if($pidFile){
            $ppid = posix_getpid();
            $pids[]=$ppid;
            file_put_contents($pidFile,implode("|",$pids));
        }

        \swoole_process::signal(SIGTERM, function() use($workers){
            exit(0);
        });

        \swoole_process::signal(SIGINT, function() {
            exit(0);
        });

        \swoole_process::wait(false);
        return $workers;
    }

}