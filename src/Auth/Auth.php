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

use Kerisy\Core\Object;
use Kerisy\Auth\Contract as AuthContract;

/**
 * Class Auth
 *
 * @package Kerisy\Auth
 */
class Auth extends Object implements AuthContract
{
    /**
     * The class that implements Authenticatable interface.
     *
     * @var \Kerisy\auth\Authenticatable
     */
    public $model;

    /**
     * @inheritDoc
     */
    public function validate(array $credentials = [])
    {
        $class = $this->model;

        $password = isset($credentials['password']) ? $credentials['password'] : null;
        unset($credentials['password']);

        $user = $class::findIdentity($credentials);

        return $user && $user->validatePassword($password) ? $user : false;
    }

    /**
     * @inheritDoc
     */
    public function attempt(array $credentials = [])
    {
        $user = $this->validate($credentials);
        if ($user) {
            $this->login($user, false);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function once(array $credentials = [])
    {
        $user = $this->validate($credentials);
        if ($user) {
            $this->login($user, true);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function login(Authenticatable $user, $once = false)
    {
        $request = request();

        if (!$once) {
            $session = session()->put(['auth_id' => $user->getAuthId()]);
            $request->session = $session;
        }

        $request->user($user);
    }

    /**
     * @inheritDoc
     */
    public function logout($sessionId)
    {
        return session()->destroy($sessionId);
    }

    /**
     * @inheritDoc
     */
    public function who($sessionId)
    {
        $class = $this->model;

        if ($bag = session()->get($sessionId)) {
            return $class::findIdentity($bag->get('auth_id'));
        }
    }
}
