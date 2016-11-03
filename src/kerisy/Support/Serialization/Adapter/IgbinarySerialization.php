<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
 */

namespace Kerisy\Support\Serialization\Adapter;

use Kerisy\Support\Serialization\SerializationAbstract;

class IgbinarySerialization extends SerializationAbstract
{
    /**
     * 序列
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function format($data)
    {
        if (!function_exists("igbinary_serialize")) {
            throw new \Exception("igbinary ext not found");
        }
        return $this->getSendContent(igbinary_serialize($data));
    }

    /**
     * 反序列
     * @param $data
     * @return null
     * @throws \Exception
     */
    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }

        if (!function_exists("igbinary_unserialize")) {
            throw new \Exception("msgpack ext not found");
        }

        $result = igbinary_unserialize($body);
        return $result;
    }
}