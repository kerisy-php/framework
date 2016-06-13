<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */
namespace Kerisy\Rpc\Server;

use Kerisy\Core\Exception;
use Kerisy\Rpc\Core\Tool;
use \Google\FlatBuffers\ByteBuffer;

abstract class Base extends \Kerisy\Server\Base{

    public function send($server, $fd, $data,$requestData) {
        $data = Tool::binFormat($data,$requestData['bufferRouteMatch'],$requestData['bufferCompressType']);
        $server->send($fd, $data);
        $server->close($fd);
    }

    public function  prepareRequest($data){

        if(!isset($data['content'])){
            throw new Exception("request is null");
        }
        $dataBuffer = ByteBuffer::wrap($data['content']);
        $routeData = \Kerisy\Rpc\Core\Hook::fire($data['bufferRouteMatch'],$dataBuffer);
        list($path,$params) = $routeData;
        if(!$path){
            throw new Exception("path is null");
        }
        
        $requestData = array();
        $requestData['path'] = $path;
        $requestData['params'] = $params;
        $requestData['method'] = "post";
        return app()->makeRequest($requestData);
    }
}
