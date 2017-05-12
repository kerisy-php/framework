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

namespace Kerisy\Support\Serialization\Adapter;

use Kerisy\Support\Arr;
use Kerisy\Support\Serialization\SerializationAbstract;

class StringSerialization extends SerializationAbstract
{
    /**
     * 序列
     * @param $data
     * @return string
     */
    public function format($data)
    {
        return $this->getSendContent($data);
    }

    /**
     * 反序列
     * @param $data
     * @return null
     */
    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }
        return $body;
    }

    /**
     * 常规序列化
     * @param $data
     * @return mixed
     */
    public function trans($data)
    {
        return $data;
    }

    /**
     * 常规反序列化
     * @param $data
     * @return mixed
     */
    public function xtrans($data)
    {
        return $data;
    }
    
}

