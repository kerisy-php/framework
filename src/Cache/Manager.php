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

namespace Kerisy\Cache;

use Kerisy\Core\Object;
use Kerisy\Cache\Engine\Contract as StorageContract;

/**
 * The Session Manager
 *
 * @package Kerisy\Session
 */
class Manager extends Object
{
    /**
     * The backend session storage.
     *
     * @var array|StorageContract
     */
    public $engine;
    /**
     * How long the session should expires, defaults to 15 days.
     *
     * @var int
     */
    public $expires = 1296000;


    public function init()
    {
        if (!$this->engine instanceof StorageContract) {
            $this->engine = make($this->engine);
        }

        $this->engine->timeout($this->expires);
    }

    /**
     * @inheritDoc
     */
    public function put($attributes = [])
    {

        $id = md5(microtime(true) . uniqid('', true) . uniqid('', true));

        return $this->engine->write($id, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->engine->read($id);
    }

    /**
     * @inheritDoc
     */
    public function set($id, $attributes)
    {

        return $this->engine->write($id, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        return $this->engine->destroy($id);
    }

    public function __call($method, $args)
    {
        return $this->storage->$method($args);
    }
}
