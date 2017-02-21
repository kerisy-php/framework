<?php
/**
 * 客户端
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


class MonitorClient
{
    //服务器主机
    private $host = null;

    //服务器端口
    private $port = null;

    //超时时间
    private $timeOut = 0.5;

    //数据序列化
    private $serialization = null;

    /**
     * 构造函数
     *
     * @param $host 主机
     * @param $port 端口
     * @param float $timeOut 超时时间,单位是s
     * @param obj $serialization 压缩解压缩工具
     */
    function __construct($host, $port, $serialization = null, $timeOut = 0.5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeOut = $timeOut;
        $this->serialization = $serialization;
    }

    /**
     * 异步数据发送
     *
     * @param $msg
     * @return mixed
     */
    public function send($msg)
    {
        if ($this->serialization) $msg = $this->serialization->format($msg);

        $client = new \swoole_client(SWOOLE_SOCK_UDP, SWOOLE_SOCK_ASYNC);

        $client->on('connect', function ($cli) use ($msg) {
            $cli->send($msg);
        });

        $client->on("receive", [$this, "onReceive"]);
        $client->on('close', [$this, 'onClose']);
        $client->on('error', [$this, 'onError']);

        $client->connect($this->host, $this->port, $this->timeOut, true);
        
        $client->close(true);
    }

    /**
     * 错误
     * @param $serv
     * @param $fd
     */
    public function onError($client)
    {
    }


    /**
     * 关闭
     * @param $server
     * @param $fd
     */
    public function onClose($client)
    {
        echo "Monitor Client Connection lose\n";
    }


    /**
     * 接收
     * @param $client
     * @param $data
     */
    public function onReceive($client, $data)
    {
        
    }



}