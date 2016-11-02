<?php
/**
 * rpc server
 *
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午6:51
 */
namespace Trendi\Rpc;

use Trendi\Foundation\Application;
use Trendi\Server\SocketInterface;
use Trendi\Server\SocketServer;
use Trendi\Coroutine\Event;
use Trendi\Support\ElapsedTime;

class RpcServer implements SocketInterface
{
    private $root = null;
    private $config = null;
    private $serialize = null;
    private $server = null;
    private $serverName = null;

    public function __construct($server, $serialize, $config, $root, $serverName = "trendi")
    {
        $this->config = $config;
        $this->root = $root;
        $this->serialize = $serialize;
        $this->server = $server;
        $this->serverName = $serverName;
    }

    public function start()
    {
        $tcpServer = new SocketServer($this->server, $this->config['server'], $this, "rpc", $this->serverName);
        $tcpServer->start();
    }

    public function bootstrap()
    {
        $obj = new Application($this->root);
        $obj->rpcBootstrap();
    }
    
    public function getSerialize()
    {
        return $this->serialize;
    }

    public function perform($data, $serv, $fd, $from_id)
    {
        $result = $this->serialize->matchAndRun($data);
        $serv->send($fd, $result);
        $serv->close($fd);
    }
}