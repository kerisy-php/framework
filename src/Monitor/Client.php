<?php
/**
 *  监控数据发送客户端
 * User: wangkaihui
 * Date: 16/8/11
 * Time: 上午11:33
 */

namespace Kerisy\Monitor;


class Client
{
    //服务器主机
    private $host = null;

    //服务器端口
    private $port = null;

    //超时时间
    private $timeOut = 0.5;


    /**
     * 构造函数
     *
     * @param $host 主机
     * @param $port 端口
     * @param float $timeOut 超时时间,单位是s
     */
    function __construct($host, $port, $timeOut = 0.5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeOut = $timeOut;
    }

    /**
     * 数据发送
     *
     * @param $msg
     * @return mixed
     */
    public function send($msg)
    {
        $client = new \swoole_client(SWOOLE_SOCK_UDP, SWOOLE_SOCK_ASYNC);

        $client->on('connect', function ($cli) use ($msg) {
            $cli->send($msg);
        });
        $client->on("receive", [$this, "onReceive"]);
        $client->on('close', [$this, 'onClose']);
        $client->on('error', [$this, 'onError']);

        return $client->connect($this->host, $this->port, $this->timeOut, true);
    }


    /**
     * 错误
     * @param $serv
     * @param $fd
     */
    public function onError($serv, $fd)
    {

    }


    /**
     * 关闭
     * @param $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {

    }


    /**
     * 接收
     * @param $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive($serv, $fd, $from_id, $data)
    {

    }


}