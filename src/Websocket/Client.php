<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Server
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\WebSocket;


use Kerisy\Core\Exception;

class Client extends Base
{
    public function __construct($host = '127.0.0.1', $port = 8080, $path = '/', $origin = null)
    {
        parent::__construct($host, $port, $path, $origin);
    }

    public function get($path, $params=[])
    {
        $requestData = [];
        $requestData[] = $path;
        $requestData[] = $params;
        $jsonStr = json_encode($requestData);
        try{
            $data = $this->connect();
            if(!$data) throw new Exception("websocket_connect_error:连接服务器失败!");
            $this->send($jsonStr);
            $str = $this->recv();
            return $str;
        }catch (Exception $e){
            throw $e;
        }
    }
}