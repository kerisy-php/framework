<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/2
 */
namespace  Kerisy\Rpc\Core;

class Tool{

static function binFormat($bufferData,$routeMatch=0,$compressType=0){
//    echo bin2hex($bufferData)."\r\n";
    $packRouteMatch = pack("n",$routeMatch);
    $packType = pack("C",$compressType);
    $len = strlen($packType.$packRouteMatch.$bufferData);
    $packLen = pack("N",$len);
    return $packLen.$packType.$packRouteMatch.$bufferData;
}

    static function getParseInfo($bufferData){
//        echo bin2hex($bufferData);
//        echo "\r\n";
        $bufferLenPack = substr($bufferData,0,4);
//        echo bin2hex($bufferLenPack);
//        echo "\r\n";
        $bufferLen = current(unpack("N",$bufferLenPack));
        $bufferCompressTypePack = substr($bufferData,4,1);
//        echo bin2hex($bufferCompressTypePack);
//        echo "\r\n";
        $bufferRouteMatchPack = substr($bufferData,5,2);
        $bufferRouteMatch = current(unpack("n",$bufferRouteMatchPack));
//        echo bin2hex($bufferRouteMatchPack);
//        echo "\r\n";
        
        $bufferCompressType = current(unpack("C",$bufferCompressTypePack));
        $content = substr($bufferData,7);
//        echo bin2hex($content);
//        echo "\r\n";
//        print_r(array("bufferlen"=>$bufferLen,"bufferCompressType"=>$bufferCompressType,"content"=>$content));
        return array("bufferlen"=>$bufferLen,"bufferCompressType"=>$bufferCompressType,"bufferRouteMatch"=>$bufferRouteMatch,"content"=>$content);
    }

    /**
     * 分析流
     * @param $bufferData
     * @param int $compressType
     */
    static function parse($bufferData){
        $arr = self::getParseInfo($bufferData);
        return isset($arr['content'])?$arr['content']:"";
    }

}