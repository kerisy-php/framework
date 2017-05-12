<?php
/**
 * rpc controller
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

use Kerisy\Support\ElapsedTime;
use Kerisy\Support\Arr;

class Controller
{

    const RESPONSE_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;

    private $server = null;
    private $fd = null;
    private $params = null;


    public function __construct($server, $fd, $params)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->params = $params;
    }


    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key, $default=null)
    {
        return isset($this->params[$key])?$this->params[$key]:$default;
    }

    
    /**
     * 数据返回
     * 
     * @param $data
     * @param int $errorCode
     * @param string $errodMsg
     * @return array
     */
    public function render($data, $errorCode = self::RESPONSE_CODE, $errorMsg = '')
    {
        $elapsedTime = ElapsedTime::runtime("rpc_sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['errorCode'] = $errorCode;
        $result['errorMsg'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        return $result;
    }

    /**
     * @param $data
     * @param int $errorCode
     * @param string $errodMsg
     */
    public function response($data, $errorCode = self::RESPONSE_CODE, $errorMsg = '')
    {
        $data = $this->render($data, $errorCode, $errorMsg);
        $config = config()->get("server.rpc");
        $defaultConfig = [
            "serialization" => 1,
            'package_body_offset' => 4,
        ];

        $configServer = Arr::merge($defaultConfig, $config['server']);
        $obj = new RpcSerialization($configServer['serialization'], $configServer['package_body_offset']);
        $data = $obj->format($data);
        $this->server->send($this->fd, $data);
//        $this->server->close($this->fd);
    }

    public function responseError($errorMsg ='', $errorCode=self::RESPONSE_NORMAL_ERROR_CODE)
    {
        return $this->response("", $errorCode, $errorMsg);
    }

    public function responseSuccess($data)
    {
        return $this->response($data);
    }

    public function fbResponse($data)
    {
        $obj = new RpcSerialization(6, 4);
        $data = $obj->format($data);
        $this->server->send($this->fd, $data);
    }
    
}