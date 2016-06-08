<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Session
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Session;

use Kerisy\Core\InvalidConfigException;
use Kerisy\Core\Object;
use Kerisy\Session\Contract as SessionContract;

/**
 * Class FileStorage
 *
 * @package Kerisy\Session
 */
class MemcachedStorage extends Object implements StorageContract
{
    public $host = "127.0.0.1";
    public $port = 11211;
    public $prefix = "session_";

    protected $timeout = 3600;

    private $_memcache;

    public function init()
    {
        $this->_memcache = new \Memcached();
        $this->_memcache->addServers([[$this->host, $this->port]]);
    }

    public function getPrefixKey($id)
    {
        return $this->prefix . $id;
    }

    /**
     * @inheritDoc
     */
    public function read($id)
    {
        return $this->_memcache->get($this->getPrefixKey($id));
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data)
    {
        return $this->_memcache->set($this->getPrefixKey($id), $data, $this->timeout) !== false;
    }

    /**
     * Destroy session by id.
     *
     * @param $id
     * @return boolean
     */
    public function destroy($id)
    {
        return $this->_memcache->delete($this->getPrefixKey($id)) !== false;
    }

    /**
     * @inheritDoc
     */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
