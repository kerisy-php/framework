<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
 */

namespace Kerisy\Support\Serialization\Adapter;

use Kerisy\Support\Serialization\SerializationAbstract;

class DefaultSerialization extends SerializationAbstract
{

    /**
     * 序列
     * @param $data
     * @return string
     */
    public function format($data)
    {
        $resultData = serialize($data);

        return $this->getSendContent($resultData);
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
        $result = unserialize($body);
        return $result;
    }
}