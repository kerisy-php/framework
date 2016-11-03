<?php
/**
 *  数据序列化处理以及route 匹配
 * 
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:31
 */

namespace Kerisy\Rpc;

use Kerisy\Mvc\Route\RouteMatch;
use Kerisy\Rpc\Exception\InvalidArgumentException;
use Kerisy\Support\Serialization\Serialization;

class RpcSerialization
{

    private $serializeObj = null;

    public function __construct($serializeType = 1, $bodyOffset = 4)
    {
        $this->serializeObj = Serialization::get($serializeType);
        $this->serializeObj->setBodyOffset($bodyOffset);
    }

    public function matchAndRun($data)
    {
        $result = $this->serializeObj->xformat($data);
        if (!$result) {
            throw new InvalidArgumentException(" received body parse fail");
        }

        list($url, $params) = $result;
        $content = RouteMatch::getInstance()->runRpc($url, $params);
        return $this->serializeObj->format($content);
    }

}