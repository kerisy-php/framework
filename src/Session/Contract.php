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
 * Interface Contract
 * 
 * @package Kerisy\Session
 */
interface Contract
{
    /**
     * Put a new session into storage.
     *
     * @param array $attributes
     * @return Session The newly created session object
     */
    public function put($attributes = []);

    /**
     * Get a session by session id.
     *
     * @param $id
     * @return Session
     */
    public function get($id);


    /**
     * Set session with new attributes.
     *
     * @param $id
     * @param $attributes
     * @return boolean
     */
    public function set($id, $attributes);

    /**
     * Destroy specified session.
     *
     * @param $id
     * @return boolean
     */
    public function destroy($id);
}