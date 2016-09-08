<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/14
 */
namespace Kerisy\Tool;

use Kerisy\Core\Exception;

class FileLog{

    /**
     * 异步写日志
     * @param $data
     * @param string $filename
     * @return bool
     * @throws Exception
     */
    static function info($data,$filename="log.log"){
        $configObj = config("config");
        $path = $configObj->get("filelogpath");
        if(!$path){
            throw new Exception("filelogpath not config");
        }
        $path = $path."/".KERISY_ENV."/".date('Y-m-d')."/";
        if(! is_dir($path)) mkdir($path, 0777, true);
        $filePath = $path.$filename;
        static $pid;
        $data = is_string($data)?$data:json_encode($data);
        !$pid && $pid=function_exists('posix_getpid')?posix_getpid():mt_rand(1,9999);
        $ip = self::get_server_ip();
        $time = \Kerisy\Tool\RunTime::runtime();
        $newData = "[".$ip."]"."[".$pid."]"."[".date('Y-m-d H:i:s')."][cost:".$time."]".$data;
        swoole_async_write($filePath,$newData."\r\n");
        return true;
    }


    static function get_server_ip(){
        if(isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'])
            return $_SERVER['SERVER_ADDR'];
        if(isset($_SERVER['HOSTNAME']) && $_SERVER['HOSTNAME']){
            return gethostbyname($_SERVER['HOSTNAME']);
        }
        return "127.0.0.1";
    }
    
}