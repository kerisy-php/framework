<?php
/**
 * 序列化
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

namespace Kerisy\Monitor;

use Kerisy\Support\Serialization\Serialization;

class MonitorSerialization
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
        return $this->serializeObj->format($data);
    }
}