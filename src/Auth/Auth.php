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

use App\User\Model\User;
use Kerisy\Core\Object;
use Kerisy\Auth\Contract as AuthContract;
use Kerisy\Contracts\Auth\Authenticatable;

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
     * @var \Kerisy\Contracts\Auth\Authenticatable
     */
    public $model;

    /**
     * @inheritDoc
     */
    public function validate(array $credentials = [])
    {
        $class = $this->model;

        $user = $class::findIdentity($credentials['uid']);
        return $user && $user->retrieveByToken($credentials) ? $user : false;
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
        $token = $request->get('token');
        if (!$once) {
//            $session = session()->put($user->updateToken($request));
            session()->set($token, $user->updateToken($request));
            $request->session = $user;
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
            return $class::findIdentity($bag['uid']);
        }
    }

    public function check()
    {
        $obj = $this->who(request()->get('token'));
        return !is_null($obj) && ($obj instanceof User);
    }
}
