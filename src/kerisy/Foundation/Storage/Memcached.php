<?php
/**
 * memcached 客户端
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
use Memcached as MemcacheExt;
use Memcache;

class Memcached
{

    protected static $conn=null;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * 初始化
     */
    private function initialize()
    {
        if(self::$conn) return ;
        $config = Config::get("storage.server.memcache");
        if(!$config) throw new ConfigNotFoundException("storage.server.memcache not config");

        if (class_exists("Memcached")) {
            $memcached = new MemcacheExt;
            if (is_array($config)) {
                foreach ($config as $v) {
                    $memcached->addServer(
                        $v['hostname'],
                        $v['port'],
                        $v['weight']
                    );
                }
            } else {
                $memcached->addServer(
                    $config['hostname'],
                    $config['port'],
                    $config['weight']
                );
            }
            $memcached->setOption(MemcacheExt::OPT_DISTRIBUTION, MemcacheExt::DISTRIBUTION_CONSISTENT);
            $memcached->setOption(MemcacheExt::OPT_HASH, MemcacheExt::HASH_CRC);
        } else {
            $memcached = new Memcache();
            if (is_array($config)) {
                foreach ($config as $name => $cache_server) {
                    $memcached->connect($cache_server['hostname'], $cache_server['port'], $cache_server['weight']);
                    break;
                }
            } else {
                $memcached->connect(
                    $config['hostname'],
                    $config['port'],
                    $config['weight']
                );
            }
        }
        return self::$conn = $memcached;
    }

    public function __call($name, $arguments)
    {
        try {
            if ($arguments) {
                return self::$conn->$name(...$arguments);
            } else {
                return self::$conn->$name();
            }
        } catch (\Exception $e) {
            Log::error(SupportException::formatException($e));
        }catch (\Error $e) {
            Log::error(SupportException::formatException($e));
        }
    }

}