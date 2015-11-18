<?php
/**
 * @project            Kerisy Framework
 * @author             Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright         (c) 2015 putao.com, Inc.
 * @package            kerisy/framework
 * @create             2015/11/11
 * @version            2.0.0
 */

namespace Kerisy\Cache;

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
     * @param array $data
     * @return boolean
     */
    public function write($id, array $data);


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
