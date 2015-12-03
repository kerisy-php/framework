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

use Kerisy\Core\Object;
use Kerisy\Session\Contract as SessionContract;

/**
 * The Session Manager
 *
 * @package Kerisy\Session
 */
class Manager extends Object implements SessionContract
{
    /**
     * The backend session storage.
     *
     * @var array|StorageContract
     */
    public $storage;
    /**
     * How long the session should expires, defaults to 15 days.
     *
     * @var int
     */
    public $expires = 1296000;


    public function init()
    {
        if (!$this->storage instanceof StorageContract) {
            $this->storage = make($this->storage);
        }

        $this->storage->timeout($this->expires);
    }

    /**
     * @inheritDoc
     */
    public function put($attributes = [])
    {
        if ($attributes instanceof Session) {
            $attributes = $attributes->all();
        }

        $id = md5(microtime(true) . uniqid('', true) . uniqid('', true));

        $this->storage->write($id, $attributes);

        return new Session($attributes, ['id' => $id]);
    }

    /**
     * @inheritDoc & <haoyanfei@outlook.com>
     */
    public function get($id)
    {
//        $data = $this->storage->read($id);
//        if ($data) {
//            return new Session($data, ['id' => $id]);
//        }
        return $this->storage->read($id);
    }

    /**
     * @inheritDoc
     */
    public function set($id, $attributes)
    {
        if ($attributes instanceof Session) {
            $attributes = $attributes->all();
        }

        return $this->storage->write($id, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        return $this->storage->destroy($id);
    }
}
