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
use Kerisy\Http\Response;

/**
 * BasicAccess middleware.
 *
 * @package Kerisy\Auth\Middleware
 */
class ClientAccount implements MiddlewareContract
{
    /**
     * The user identity name that used to authenticate.
     *
     * @var string
     */
    public $identity = 'id';

    /**
     * @param Response $response
     */
    public function handle($response)
    {
        if (!auth()->check()) {
            response()->json(['httpCode' => '4010', 'msg' => '未登录']);
        }
    }
}
