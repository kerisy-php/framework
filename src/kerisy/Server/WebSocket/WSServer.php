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


use Kerisy\Server\HttpServer;
use Kerisy\Support\ElapsedTime;
use Kerisy\Mvc\Route\RouteMatch;
use swoole_http_server as SwooleServer;

class WSServer extends HttpServer
{
    const RESPONSE_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;
    public static $allFd = [];
    
    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $serverName)
    {
        parent::__construct($swooleServer, $config, $adapter, $serverName);
    }

    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        //todo 保存到redis
        self::$allFd[$request->fd] = $request->fd;//首次连上时存起来
    }

    public function onWsMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        if($frame->data){
            list($path, $params) = json_decode($frame->data, true);
            RouteMatch::getInstance()->runSocket($path, $params, $server,$frame->fd);
        }else{
            $content = $this->render('', self::RESPONSE_NORMAL_ERROR_CODE, "收到的数据为空!");
            $server->push($frame->fd, $this->render($content));
        }
    }

    public function onClose(SwooleServer $server, $fd)
    {
        unset(self::$allFd[$fd]);
    }

    protected function render($data='', $errorCode = self::RESPONSE_CODE, $errodMsg = '')
    {
        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['errorCode'] = $errorCode;
        $result['errodMsg'] = $errodMsg;
        $result['elapsedTime'] = $elapsedTime;
        return json_encode($result);
    }

}