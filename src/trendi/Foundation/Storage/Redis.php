<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午12:49
 */

namespace Trendi\Foundation\Storage;

use Trendi\Config\Config;
use Trendi\Foundation\Exception\ConfigNotFoundException;
use Trendi\Pool\PoolClient;
use Trendi\Support\Log;
use Predis\Client;

class Redis
{
    protected static $client = null;
    //适配器类型
    const ADAPTER_DEFAULT = "default";
    const ADAPTER_POOL = "pool";
    private $type = self::ADAPTER_DEFAULT;
    
    protected static $conn = null;

    public function __construct()
    {
        $type = Config::get("app.adapter.redis");
        $this->type = $type;
        if($this->type == self::ADAPTER_DEFAULT){
            $this->initializeDefault();
        }else{
            $this->initializePool(); 
        }
    }
    
    protected function initializeDefault()
    {
        if(self::$conn) return ;
        $config = Config::get("storage.redis");
        $servers = $config['servers'];
        $options = $config['options'];
        try {
            self::$conn = new Client($servers, $options);
        } catch (\Exception $e) {
            throw $e;
        }catch (\Error $e) {
            throw $e;
        }
    }

    protected function initializePool()
    {
        if(self::$client) return ;
//        Log::sysinfo("new redis client conn");
        $config = Config::get("client.pool");
        if (!$config) {
            throw new ConfigNotFoundException("client.pool not config");
        }

        self::$client = new PoolClient($config['host'], $config['port'], $config['serialization'], $config);
    }

    public function __call($name, $arguments)
    {
        if($this->type == self::ADAPTER_DEFAULT){
            try {
                if ($arguments) {
                    return self::$conn->$name(...$arguments);
                } else {
                    return self::$conn->$name();
                }
            } catch (\Exception $e) {
                throw $e;
            }catch (\Error $e) {
                throw $e;
            }
            
        }else{
            $params = [
                $name,
                $arguments
            ];
            $data = self::$client->get("redis", $params);
            return $data; 
        }
    }

    public function __destruct()
    {
//        $this->client->close();
    }
}