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
            $config['log_file'] = $this->logFile;
        }

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
        
        return $server;
    }

    public function onClose($server, $fd){
        $server->close($fd);
    }
    
    public function onWorkerStart(){
        echo("application started\r\n");
        cli_set_process_title($this->name . ': worker');
    }

    public function onWorkerStop(){
        cli_set_process_title($this->name . ': worker stop');
    }
    
    public function onServerStart($server){
        cli_set_process_title($this->name . ': master');
        if ( $this->pidFile ) {
            file_put_contents($this->pidFile , $server->master_pid);
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
