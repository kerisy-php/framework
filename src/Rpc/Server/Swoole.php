<?php
/**
 * @author             kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package            kerisy/framework
 * @since              2016/05/31
 */

namespace Kerisy\Rpc\Server;

/**
 * A Swoole based server implementation.
 *
 * @package Kerisy\Server
 */
use Kerisy\Log\Logger;
use Kerisy\Log\StreamTarget;
use Kerisy\Rpc\Core\Tool;

class Swoole extends Base
{

    /**
     * The number of requests each process should execute before respawning, This can be useful to work around
     * with possible memory leaks.
     *
     * @var int
     */
    public $maxRequests = 65535;

    /**
     * The number of workers should be started to serve requests.
     *
     * @var int
     */
    public $numWorkers;

    /**
     * Detach the server process and run as daemon.
     *
     * @var bool
     */
    public $asDaemon = FALSE;

    public $taskWorkerNum = 4;
    protected $memoryLimit = "1024m";

    public $reactorNum=4;

    /**
     * Specifies the path where logs should be stored in.
     *
     * @var string
     */
    public $logFile;


    private function normalizedConfig()
    {
        $config = [ ];

        $config['max_request'] = $this->maxRequests;
        $config['daemonize'] = $this->asDaemon;

        if ( $this->numWorkers ) {
            $config['worker_num'] = $this->numWorkers;
        }

        if ( $this->logFile ) {
            $config['log_file'] = $this->logFile?$this->logFile:"/tmp/rpcserver.log";
        }
//
//        $config['dispatch_mode'] = 3;
//        $config['open_eof_check'] = true;
//        $config['package_eof'] = "\r\n\*";
//        $config['open_eof_split'] = true;
//        $config['task_worker_num'] = $this->taskWorkerNum;

        $config['open_length_check'] = true;
        $config['dispatch_mode'] = 1;
        $config['package_length_type'] = 'N';
        $config['package_length_offset'] = 0;
        $config['package_body_offset'] = 4;
        $config['package_max_length'] = 2000000;

        $config['reactor_num'] = $this->reactorNum;
        
        return $config;
    }

    private function createServer()
    {
        $server = new \swoole_server($this->host, $this->port,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);

        $server->set($this->normalizedConfig());

        $server->on('start' , [ $this , 'onServerStart' ]);
        $server->on('shutdown' , [ $this , 'onServerStop' ]);
        $server->on("receive", [$this,"onServerReceive"]);

        $server->on('workerStart' , [ $this , 'onWorkerStart' ]);
        $server->on('workerStop' , [ $this , 'onWorkerStop' ]);
        $server->on('close' , [ $this , 'onClose' ]);

        $server->on('task' , [ $this , 'onTask' ]);

        $server->on('finish' , [ $this , 'onFinish' ]);

        //onFinish

        
        return $server;
    }

    function onTask($serv, $task_id, $from_id, $data){
    }

    function onFinish(){

    }

    public function onClose($server, $fd){
    }
    
    public function onWorkerStart($swooleServer, $workerId){
        if (function_exists("apc_clear_cache")) {
            apc_clear_cache();
        }

        if (function_exists("opcache_reset")) {
            opcache_reset();
        }

        echo("application started\n");
        swoole_set_process_name($this->name . ":worker");
    }

    public function onWorkerStop(){
        echo("worker stop\n");
    }
    
    public function onServerStart($server){
        swoole_set_process_name($this->name . ":master");
        if ( $this->pidFile ) {
            file_put_contents($this->pidFile , $server->master_pid);
        }
        
        if($this->memoryLimit){
            ini_set("memory_limit", $this->memoryLimit);
            $this->memoryCheck();
        }
    }

    public function onServerStop(){
        if ( $this->pidFile ) {
            unlink($this->pidFile);
        }
    }
    
    public function onServerReceive($server, $fd, $from_id, $data){
        $this->startApp();
        $requestData = Tool::getParseInfo($data);
        $resData = $this->prepareRequest($requestData);
        $content = "";
        if($resData){
            $res = $this->handleRequest($resData);
            $content = $res->content();
        }
        $this->send($server,$fd,$content,$requestData);
        $this->stopApp();
    }
    
    public function run()
    {
        $server = $this->createServer();

        $server->start();
    }
}
