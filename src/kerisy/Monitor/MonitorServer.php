<?php
/**
 * 监控接收数据
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

namespace Kerisy\Monitor;


use Kerisy\Coroutine\Event;
use Kerisy\Server\SocketInterface;
use Kerisy\Foundation\Bootstrap\RouteBootstrap;
use Kerisy\Server\SocketServer;

class MonitorServer implements SocketInterface
{
    private $config = null;
    private $serialize = null;
    private $server = null;
    private $serverName = null;
    private $performClass = null;

    public function __construct($server, $serialize, $config, $performClass, $serverName = "trensy-monitor")
    {
        $this->config = $config;
        $this->serialize = $serialize;
        $this->server = $server;
        $this->serverName = $serverName;
        $this->performClass = $performClass;
    }

    public function start()
    {
        $tcpServer = new SocketServer($this->server, $this->config['server'], $this, "monitor", $this->serverName);
        $tcpServer->start();
    }

    public function bootstrap()
    {
        RouteBootstrap::getInstance();
    }

    public function getSerialize()
    {
        return $this->serialize;
    }
    
    public function perform($data, $serv, $fd, $from_id)
    {
        if($this->serialize){
            $result = $this->serialize->xtrans($data);
        }else{
            $result = $data;
        }
        if (!$result) {
            throw new \Exception(" received body parse fail");
        }
        //monitor_receive
        if($this->performClass){
            $obj = new $this->performClass;
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
            $obj->perform([$fd, $from_id,$result]);
        }
        $serv->close($fd);
    }
}