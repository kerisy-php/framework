<?php
/**
 * User: Peter Wang
 * Date: 16/12/16
 * Time: 上午9:57
 */

namespace Kerisy\WebSocket;

use Kerisy\Core\MiddlewareTrait;

class Controller
{
    const RESPONSE_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;

    protected $server = null;
    protected $fd = null;

    use MiddlewareTrait;

    public function __construct($server, $fd)
    {
        $this->server = $server;
        $this->fd = $fd;
    }


    public function send($data, $errorCode = self::RESPONSE_CODE, $errodMsg = '')
    {
        $result = [];
        $result['result'] = $data;
        $result['errorCode'] = $errorCode;
        $result['errodMsg'] = $errodMsg;
        $jsonResult = json_encode($result);
        return $this->server->push($this->fd, $jsonResult);
//        $this->server->close($this->fd);
    }

}