<?php
/**
 * rpc server
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
namespace Kerisy\Rpc;

use Kerisy\Foundation\Application;
use Kerisy\Server\SocketInterface;
use Kerisy\Server\SocketServer;
use Kerisy\Coroutine\Event;
use Kerisy\Support\ElapsedTime;

class RpcServer implements SocketInterface
{
    private $root = null;
    private $config = null;
    private $serialize = null;
    private $server = null;
    private $serverName = null;

    public function __construct($server, $serialize, $config, $root, $serverName = "kerisy")
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