<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Cache
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Cache\Engine;

use Kerisy\Core\InvalidConfigException;
use Kerisy\Core\Object;
use Kerisy\Cache\Engine\Contract as EngineContract;

/**
 * Class Memcache
 *
 * @package Kerisy\Cache
 */
class MemcachedEngine extends Object implements EngineContract
{
    public $host = "127.0.0.1";
    public $port = 11211;
    public $prefix = "cache_";

    protected $timeout = 3600;

    private $_memcache;

    public function init()
    {
        $this->_memcache = new \Memcached();
        if (!$this->_memcache->addServer($this->host, $this->port)) {
            throw new InvalidConfigException("The memcached host '{$this->host}' error.");
        }
    }

    public function getPrefixKey($key)
    {
        return $this->prefix . $key;
    }

    /**
     * @inheritDoc
     */
    public function read($key)
    {
        $data = $this->_memcache->get($this->getPrefixKey($key));
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function write($key, $data, $ttl = 0)
    {
        return $this->_memcache->set($this->getPrefixKey($key), $data, $ttl = 0) !== false;
    }

    /**
     * Destroy cache by key.
     *
     * @param $id
     * @return boolean
     */
    public function destroy($key)
    {
        return $this->_memcache->delete($this->getPrefixKey($key)) !== false;
    }

    /**
     * @inheritDoc
     */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function __call($method, $args)
    {
        $count = count($args);
        switch ($count) {
            case 1:
                $this->_memcache->$method($this->getPrefixKey($args[0]));
                break;
            case 2:
                $this->_memcache->$method($this->getPrefixKey($args[0]), $args[1]);
                break;
            case 3:
                $this->_memcache->$method($this->getPrefixKey($args[0]), $args[1], $args[2]);
                break;
            case 4:
                $this->_memcache->$method($this->getPrefixKey($args[0]), $args[1], $args[2], $args[3]);
                break;
            default:
                return false;
                break;
        }
        return true;
    }
}
