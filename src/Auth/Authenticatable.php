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

use Kerisy\Http\Request;
use Kerisy\Support\Account;

/**
 * Interface Authenticatable
 *
 * @package Kerisy\Auth
 */
trait Authenticatable
{
    use Account;

    public function updateToken(Request $request)
    {
        return [
            'uid' => $request->get('uid'),
            'token' => $request->get('token')
        ];
    }

    /**
     * @auth haoyanfei<haoyf@putao.com>
     * @param $credentials [token,sign]
     * @return bool
     */
    public function retrieveByToken($credentials = [])
    {
        /**account[id,nickname,token]**/
        if ($account = $this->authorizable($credentials)) {
            return static::firstOrCreate(['id' => $credentials['uid']])->update(['username' => $credentials['username']]);
        }
        return false;
    }

    /**
     * Find model by it's identifiers.
     *
     * @param mixed $id
     * @return static
     */
    public static function findIdentity($id)
    {
        return static::where('id', $id)->first();
    }

    /**
     * Get the auth id that used to store in session.
     *
     * @return mixed
     */
    public function getAuthId()
    {
        return $this->getKey();
    }

    /**
     * Check whether the given password is correct.
     *
     * @param $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return true;
    }
}
