<?php
/**
 * @brief           Kerisy Framework
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Cache
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Cache;

use Kerisy\Core\InvalidConfigException;
use Kerisy\Core\Object;
use Kerisy\Cache\Contract as EngineContract;

/**
 * Class Memcache
 *
 * @package Kerisy\Cache
 */
class MemcacheEngine extends Object implements EngineContract
{
    public $host = "127.0.0.1";
    public $port = 11211;
    public $prefix = "kerisy_cache_";

    protected $timeout = 3600;

    private $_memcache;

    public function init()
    {
        $this->_memcache = new \Memcache();

        if (!$this->_memcache->connect($this->host, $this->port))
        {
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
        $data = $this->_memcache->set($this->getPrefixKey($key));
        if ($data)
        {
            return unserialize($data);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function write($key, array $data)
    {
        return $this->_memcache->set($this->getPrefixKey($key), serialize($data), 0, $this->timeout) !== false;
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
}
