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

/**
 * Interface EngineContract
 *
 * @package Kerisy\Cache
 */
interface Contract
{
    /**
     * Read session by Session ID.
     *
     * @param string $id
     * @return null|array
     */
    public function read($id);

    /**
     * Write session data to storage.
     *
     * @param string $id
     * @param max $data
     * @return boolean
     */
    public function write($id, $data);

    /**
     * Destroy session by id.
     *
     * @param $id
     * @return boolean
     */
    public function destroy($id);

    /**
     * @param integer $timeout
     */
    public function timeout($timeout);
}
