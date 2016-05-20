<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei<haoyf@putao.com>
 * Date: 2015/12/16
 * Time: 10:37
 */

namespace Kerisy\Support;


class Redis
{
    public $host = "127.0.0.1";
    public $port = 6379;
    public $prefix = "kerisy_redis_";

    protected $expire = 3600;

    private $redis;

    public function __construct()
    {
        if (!get_loaded_extensions('redis')) {
            throw new InvalidConfigException("not exist redis extensions error.");
        }
        $this->redis = new \Redis();
        $this->redis->connect($this->host, $this->port, $this->expire);
    }

    public function getPrefixKey($key)
    {
        return $this->prefix . $key;
    }

    public function __call($method, $args)
    {
        $count = count($args);
        switch ($count) {
            case 1:
                $result = $this->redis->$method($this->getPrefixKey($args[0]));
                break;
            case 2:
                $result = $this->redis->$method($this->getPrefixKey($args[0]), $args[1]);
                break;
            case 3:
                $result = $this->redis->$method($this->getPrefixKey($args[0]), $args[1], $args[2]);
                break;
            case 4:
                $result = $this->redis->$method($this->getPrefixKey($args[0]), $args[1], $args[2], $args[3]);
                break;
            case 5:
                $result = $this->redis->$method($this->getPrefixKey($args[0]), $args[1], $args[2], $args[3],$args[4]);
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }
}