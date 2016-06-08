<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */
namespace Kerisy\Rpc\Client;

use Google\FlatBuffers\ByteBuffer;
use Kerisy\Rpc\Core\Tool;

class Base{

    function send($client,$fnData,$sourceType,$compressType=0){
        $data = Tool::binFormat($fnData,$sourceType,$compressType);
        return $client->send($data);
    }

    /**
     * 结果处理
     * @param $response
     * @return ByteBuffer|string
     */
    function getResult($response){
        if($response){
            $result = Tool::parse($response);
        }else{
            $result = "";
        }
        $data = ByteBuffer::wrap($result);
        return $data;
    }
}