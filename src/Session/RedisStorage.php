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
 * Class RedisStorage
 *
 * @package Kerisy\Session
 */
class RedisStorage extends Object implements StorageContract
{
    public $host = "127.0.0.1";
    public $port = 6379;
    public $password = "";
    public $prefix = "session_";

    protected $timeout = 3600;

    /**
     * @var \Redis
     */
    private $_redis;

    public function init()
    {
        $this->_redis = new \Redis();

        if (!$this->_redis->connect($this->host, $this->port)) {
            throw new InvalidConfigException("The redis host '{$this->host}' error.");
        }
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
        if ($data = $this->_redis->get($this->getPrefixKey($id)))
        {
            return unserialize($data);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function write($id, array $data)
    {
        return $this->_redis->set($this->getPrefixKey($id), serialize($data), $this->timeout) !== false;
    }

    /**
     * Destroy session by id.
     *
     * @param $id
     * @return boolean
     */
    public function destroy($id)
    {
        return $this->_redis->delete($this->getPrefixKey($id)) !== false;
    }

    /**
     * @inheritDoc
     */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
