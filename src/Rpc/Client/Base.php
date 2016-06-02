<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */
namespace Kerisy\Rpc\Client;

use \Google\FlatBuffers\ByteBuffer;
use Kerisy\Rpc\Core\Tool;

class Base{
    
    function send($client,$servicePath,$params=array()){
        $data = [];
        $data['path'] = $servicePath;
        $data['params'] = $params;
        $json = json_encode($data);
        return $client->send($json);
    }

    function getResult($response){
        return ByteBuffer::wrap($response);
    }
}