<?php
/**
 * 监控接收服务器
 *
 * User: wangkaihui
 * Date: 16/8/11
 * Time: 上午11:27
 */

namespace Kerisy\Monitor;

use Kerisy\Core\Hook;

class Server extends \Kerisy\Server\Base
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


    public $reactorNum = 4;

    /**
     * Specifies the path where logs should be stored in.
     *
     * @var string
     */
    public $logFile;

    /**
     * 配置初始化
     *
     * @return array
     */
    private function normalizedConfig()
    {
        $config = [];

        $config['max_request'] = $this->maxRequests;
        $config['daemonize'] = $this->asDaemon;

        if ($this->numWorkers) {
            $config['worker_num'] = $this->numWorkers;
        }

        if ($this->logFile) {
            $config['log_file'] = $this->logFile ? $this->logFile : "/tmp/monitorserver.log";
        }


        $config['reactor_num'] = $this->reactorNum;

        return $config;
    }

    /**
     * 创建server 对象
     *
     * @return \swoole_server
     */
    private function createServer()
    {
        $server = new \swoole_server($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

        $server->set($this->normalizedConfig());

        $server->on('workerStart', [$this, 'onWorkerStart']);
        $server->on('connect', [$this, 'onConnect']);
        $server->on("receive", [$this, "onReceive"]);
        $server->on('close', [$this, 'onClose']);

        return $server;
    }

    /**
     * worker 开始
     *
     */
    public function onWorkerStart()
    {
        echo("application started\r\n");
    }

    /**
     * 连接关闭
     *
     * @param $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {

    }

    /**
     * 已连接
     *
     * @param $serv
     * @param $fd
     */
    public function onConnect($serv, $fd)
    {

    }

    /**
     * 接收
     *
     * @param $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive($serv, $fd, $from_id, $data)
    {
        Hook::fire("monitor_receive", [$fd, $from_id, $data]);
    }

    /**
     * 执行
     *
     */
    public function run()
    {
        $server = $this->createServer();

        $server->start();
    }

}