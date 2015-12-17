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

/**
 * Interface StorageContract
 *
 * @package Kerisy\Session
 */
interface StorageContract
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
    public function write($id,$data);


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
