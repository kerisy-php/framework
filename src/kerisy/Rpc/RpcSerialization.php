<?php
/**
 *  数据序列化处理以及route 匹配
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

use Kerisy\Support\Serialization\Serialization;

class RpcSerialization
{

    private $serializeObj = null;

    public function __construct($serializeType = 1, $bodyOffset = 4)
    {
        $this->serializeObj = Serialization::get($serializeType);
        $this->serializeObj->setBodyOffset($bodyOffset);
    }

    public function xformat($data)
    {
        return $this->serializeObj->xformat($data);
    }

    public function format($data)
    {
        $this->serializeObj->format($data);
    }
}