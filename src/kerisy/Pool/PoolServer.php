<?php
/**
 * 连接池服务器
 *
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 下午5:41
 */

namespace Kerisy\Pool;

use Kerisy\Foundation\Application;
use Kerisy\Pool\Exception\InvalidArgumentException;
use Kerisy\Server\SocketInterface;
use Kerisy\Server\SocketServer;
use Kerisy\Coroutine\Event;

class PoolServer implements SocketInterface
{
    private $root = null;
    private $config = null;
    private $serialize = null;
    private $server = null;
    private $serverName = null;
    private $timeOut = 10;
    private $poolWorkrNumberConfig = [];
    private $numbersTmp = [];

    /**
     * 执行
     *
     * @param $server
     * @param $serialize
     * @param $config
     * @param $root
     * @param string $serverName
     */
    public function __construct($server, $serialize, $config, $root, $serverName = "kerisy")
    {
        $this->config = $config;
        $this->timeOut = isset($config['server']['task_timeout']) ? $config['server']['task_timeout'] : $this->timeOut;
        $this->root = $root;
        $this->serialize = $serialize;
        $this->server = $server;
        $this->serverName = $serverName;
        $poolWorkrNumber = 0;
        $poolWorkrNumberConfig = $config['server']['pool_worker_number'];
        if ($poolWorkrNumberConfig) {
            foreach ($poolWorkrNumberConfig as $v) {
                $poolWorkrNumber += $v;
            }
        }
        $this->poolWorkrNumberConfig = $poolWorkrNumberConfig;
        $this->config['server']['task_worker_num'] = $poolWorkrNumber;
    }

    /**
     * 服务开始
     */
    public function start()
    {
        $tcpServer = new SocketServer($this->server, $this->config['server'], $this, "pool", $this->serverName);
        $tcpServer->start();
    }

    /**
     * 初始化
     */
    public function bootstrap()
    {
        $obj = new Application($this->root);
        $obj->poolBootstrap();
    }

    /**
     * 数据接收执行
     *
     * @param $data
     * @param $serv
     * @param $fd
     * @param $from_id
     * @throws InvalidArgumentException
     */
    public function perform($data, $serv, $fd, $from_id)
    {
        $result = $this->serialize->xformat($data);
        if (!$result) {
            $serv->send($fd, "");
            return;
        }
        list($driver, $params) = $result;
        $dstWorkerId = $this->getDstWorkerId($driver);
//        dump($driver.":".$dstWorkerId);
        $this->sendWait($driver, $params, $serv, $fd, $dstWorkerId);
    }

    /**
     * 不同连接池,分配不同task worker
     *
     * @param $taskName
     * @return int
     */
    protected function getDstWorkerId($taskName)
    {
        $taskName = strtolower($taskName);
        if (!isset($this->poolWorkrNumberConfig[$taskName])) return -1;

        if(isset($this->numbersTmp[$taskName]) && $this->numbersTmp[$taskName]) return array_pop($this->numbersTmp[$taskName]);
        $pre = 0;
        $current = 0;
        foreach ($this->poolWorkrNumberConfig as $k => $v) {
            if ($k == $taskName) {
                $current = $v;
                break;
            }
            $pre = $v;
        }
        $start = $pre;
        $end = $pre + $current - 1;
        $numbers = range($start, $end);
        //按照顺序执行,保证每个连接池子数固定
        if (!isset($this->numbersTmp[$taskName]) ||
            (isset($this->numbersTmp[$taskName])&&empty($this->numbersTmp[$taskName]))) {
            $this->numbersTmp[$taskName] = $numbers;
        }
        return array_pop($this->numbersTmp[$taskName]);
    }

    /**
     * 堵塞发送
     *
     * @param $taskName
     * @param array $params
     * @param $serv
     * @param $fd
     * @param int $dstWorkerId
     * @throws InvalidArgumentException
     */
    public function sendWait($taskName, $params = [], $serv, $fd, $dstWorkerId = -1)
    {
        if (!$fd) {
            throw new InvalidArgumentException(" receive fd is not get");
        }
        $sendData = [$taskName, $params, 0, $dstWorkerId];
        $result = $serv->taskwait($sendData, $this->timeOut, $dstWorkerId);
        list($status, $returnData, $exception) = $result;
        if ($status !== false) {
            $returnData = $this->serialize->format($returnData);
            $serv->send($fd, $returnData);
        } else {
            $this->log($exception, $returnData);
            $exception = $this->serialize->format($exception);
            $serv->send($fd, $exception);
        }
        Event::fire("clear");
    }

    /**
     * 保存失败执行记录
     *
     * @param $exception
     * @param $returnData
     */
    private function log($exception, $returnData)
    {
        //超过次数,记录日志
        $msg = date('Y-m-d H:i:s') . " " . json_encode($returnData);
        if ($exception) {
            $msg .= "\n================================================\n" .
                $exception .
                "\n================================================\n";
        }
        swoole_async_write($this->config['server']['task_fail_log'], $msg);
    }
}