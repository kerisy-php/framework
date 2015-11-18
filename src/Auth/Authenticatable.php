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

/**
 * Interface Authenticatable
 *
 * @package Kerisy\Auth
 */
interface Authenticatable
{
    /**
     * Find model by it's identifiers.
     *
     * @param mixed $id
     * @return static
     */
    public static function findIdentity($id);

    /**
     * Get the auth id that used to store in session.
     *
     * @return mixed
     */
    public function getAuthId();

    /**
     * Check whether the given password is correct.
     *
     * @param $password
     * @return boolean
     */
    public function validatePassword($password);
}
