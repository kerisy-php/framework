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

use Kerisy\Support\Serialization\SerializationAbstract;

class JsonSerialization extends SerializationAbstract
{
    /**
     * 序列
     * @param $data
     * @return string
     */
    public function format($data)
    {
        return $this->getSendContent(json_encode($data));
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

        $result = json_decode($body, true);
        return $result;
    }
}