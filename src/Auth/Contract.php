<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Auth
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Auth;


interface Contract
{
    /**
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function validate(array $credentials = []);

    /**
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function attempt(array $credentials = []);

    /**
     * @param array $credentials
     * @return Authenticatable|false
     */
    public function once(array $credentials = []);

    /**
     * Login the given user.
     *
     * @param Authenticatable $user
     * @param boolean $once Login just for once, no session will be stored.
     */
    public function login(Authenticatable $user, $once = false);

    /**
     * Logout the given user.
     *
     * @param $sessionId
     * @return boolean
     */
    public function logout($sessionId);

    /**
     * @param $sessionId
     * @return Authenticatable|null
     */
    public function who($sessionId);
}
