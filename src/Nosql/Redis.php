<?php
/**
 * 葡萄科技.
 * @copyright Copyright (c) 2015 Putao Inc.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 16/4/25 10:28
 */

namespace Kerisy\Nosql;

use Kerisy\Core\Object;

class Redis extends Object
{
    public $host = 'locahost';

    public $port = 6379;

    public $timeout = 1;

    public $database = 0;
    
    public $serializer = 1;

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @return \Redis
     * @throws \RedisException
     */
    public function connect()
    {
        if (!$this->redis) {
            $this->redis = new \Redis();
            $this->redis->connect($this->host, $this->port, $this->timeout);
            if ($this->serializer == \Redis::SERIALIZER_NONE) {
                $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            } else {
                $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            }
        }
        return $this->redis;
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->redis = null;
    }
} 