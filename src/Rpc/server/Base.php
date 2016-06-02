<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */
namespace Kerisy\Rpc\Server;

use Kerisy\Rpc\Core\Tool;

abstract class Base extends \Kerisy\Server\Base{

    public function send($server, $fd, $data) {
        if(error::$errorMsg){
            $data = error::$errorMsg;
        }
        $server->send($fd, $data);
        $server->close($fd);
    }

    public function  prepareRequest($data){
        $initData = json_decode($data,true);
        $path = isset($initData) ? $initData:"";
        if(!$path){
            error::$errorMsg = "request path is null";
            return false;
        }
        $requestData = array();
        $requestData['path'] = $initData['path'];
        $requestData['params'] = $initData['params'];
        $requestData['method'] = "post";
        
        return app()->makeRequest($requestData);
    }
}

class error{
    static $errorMsg = "";
}