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

namespace Kerisy\Foundation\Storage;

use Kerisy\Config\Config;
use Kerisy\Foundation\Exception\ConfigNotFoundException;
use Kerisy\Support\Log;
use Predis\Client;
use Kerisy\Support\Exception as SupportException;

class Redis
{
    protected static $conn = null;

    public function __construct()
    {
        $this->initializeDefault();
    }
    
    protected function initializeDefault()
    {
        if(self::$conn) return ;
        $config = Config::get("storage.server.redis");
        $servers = $config['servers'];
        if(!$servers) throw new ConfigNotFoundException("storage.server.redis.servers not config");
        $options = $config['options'];
        try {
            self::$conn = new Client($servers, $options);
        } catch (\Exception $e) {
            Log::error(SupportException::formatException($e));
        }catch (\Error $e) {
            Log::error(SupportException::formatException($e));
        }
    }

    public function __call($name, $arguments)
    {
        try {
            $result = $this->run($name ,$arguments);
            return $result;
        } catch (\Exception $e) {
            $this->reConn($e);
            if(self::$conn){
                return $this->run($name ,$arguments);
            }else{
                Log::error(SupportException::formatException($e));
            }
        }catch (\Error $e) {
            $this->reConn($e);
            if(self::$conn){
                return $this->run($name ,$arguments);
            }else{
                Log::error(SupportException::formatException($e));
            }
        }
    }

    protected function run($name ,$arguments)
    {
        if ($arguments) {
            $result = self::$conn->$name(...$arguments);
        } else {
            $result = self::$conn->$name();
        }
        if($result instanceof \Predis\Response\Status){
            $result =  $result->getPayload();
            $result = $result=='OK'?true:false;
        }

        return $result;
    }

    protected function reConn($e){
        if($e->getCode() != 0 || !stristr($e->getMessage(), 'No connections available in the pool')) {
            throw new \Exception($e->getMessage());
        }
        //重新连接
        self::$conn = [];
        $this->initializeDefault();
    }
    
}