<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
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