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

class WSClient extends BaseClient
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
        $jsonStr = Arr::my_json_encode($requestData);
        try{
            $data = $this->connect();
            if(!$data) throw new \Exception("websocket_connect_error:connect fail!");
            $this->send($jsonStr);
            $tmp = $this->recv();
            return $tmp;
        }catch (\Exception $e){
            throw $e;
        }
    }
}