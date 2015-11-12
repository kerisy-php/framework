<?php

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
