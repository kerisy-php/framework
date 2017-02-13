<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Server\WebSocket;

use Kerisy\Support\Arr;
use Kerisy\Support\ElapsedTime;

class Controller
{

    const RESPONSE_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;

    private $server = null;
    private $fd = null;

    public function __construct($server, $fd)
    {
        $this->server = $server;
        $this->fd = $fd;
    }

    /**
     * 数据返回
     * 
     * @param $data
     * @param int $errorCode
     * @param string $errodMsg
     * @return array
     */
    public function render($data, $errorCode = self::RESPONSE_CODE, $errorMsg= '')
    {
        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['errorCode'] = $errorCode;
        $result['errorMsg'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        return Arr::my_json_encode($result);
    }

    /**
     * 广播
     *
     * @param $data
     * @param int $errorCode
     * @param string $errodMsg
     */
    public function broadcast($data, $errorCode = self::RESPONSE_CODE, $errorMsg = '')
    {
        $data = $this->render($data, $errorCode, $errorMsg);
        $clients = WSServer::$allFd;
        if($clients){
            foreach ($clients as $v){
                $this->server->push($v, $data);
            }
        }
    }

    /**
     * @param $data
     * @param int $errorCode
     * @param string $errodMsg
     */
    public function response($data, $errorCode = self::RESPONSE_CODE, $errorMsg = '')
    {
        $data = $this->render($data, $errorCode, $errorMsg);
        $this->server->push($this->fd, $data);
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->server->close($this->fd);
    }
}