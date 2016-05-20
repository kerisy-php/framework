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

namespace Kerisy\Auth\Middleware;

use Kerisy\Core\MiddlewareContract;
use Kerisy\Http\Request;

/**
 * BasicAccess middleware.
 *
 * @package Kerisy\Auth\Middleware
 */
class BasicAccess implements MiddlewareContract
{
    /**
     * The user identity name that used to authenticate.
     *
     * @var string
     */
    public $identity = 'name';

    /**
     * @param Request $request
     */
    public function handle($request)
    {
        $value = $request->headers->first('Authorization');
        if (!$value) {
            return;
        }

        $parts = preg_split('/\s+/', $value);
        if (count($parts) < 2 && strtolower($parts[0]) != 'basic') {
            return;
        }

        list($username, $password) = explode(':', base64_decode($parts[1]));

        auth()->attempt([
            $this->identity => $username,
            'password' => $password,
        ]);
    }
}
