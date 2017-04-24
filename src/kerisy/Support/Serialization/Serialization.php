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

namespace Kerisy\Support\Serialization;

use Kerisy\Foundation\Shortcut;
use Kerisy\Support\Serialization\Adapter\DefaultSerialization;
use Kerisy\Support\Serialization\Adapter\HproseSerialization;
use Kerisy\Support\Serialization\Adapter\IgbinarySerialization;
use Kerisy\Support\Serialization\Adapter\JsonSerialization;
use Kerisy\Support\Serialization\Adapter\MsgPackSerialization;

class Serialization
{
    use Shortcut;
    /**
     * 获取序列方案
     * 
     * @param $type
     * @return DefaultSerialization|HproseSerialization|IgbinarySerialization|JsonSerialization|MsgPackSerialization
     */
    public static function get($type=1)
    {
        $type = intval($type);
        if($type){
            switch ($type) {
                case 1:
                    return new DefaultSerialization();
                case 2:
                    return new MsgPackSerialization();
                case 3:
                    return new IgbinarySerialization();
                case 4:
                    return new JsonSerialization();
                case 5:
                    return new HproseSerialization();
                default:
                    return new DefaultSerialization();
            }

        }else{
            if (function_exists("msgpack_pack")){
                return new MsgPackSerialization();
            }elseif(function_exists("igbinary_serialize")){
                return new IgbinarySerialization();
            }elseif(function_exists("hprose_serialize")){
                return new HproseSerialization();
            }else{
                return new DefaultSerialization();
            }
        }
    }


}