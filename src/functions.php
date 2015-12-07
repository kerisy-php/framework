<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      functions
 * @since           2015/11/11
 * @version         2.0.0
 */

use Kerisy\Di\Container;
use Kerisy\Core\InvalidConfigException;
use Kerisy\Core\HttpException;

/**
 * Shortcut helper function to create object via Object Configuration.
 *
 * @param $type
 * @param array $params
 * @return mixed
 * @throws InvalidConfigException
 */
function make($type, $params = [])
{
    if (is_string($type)) {
        return Container::getInstance()->get($type, $params);
    } elseif (is_array($type) && isset($type['class'])) {
        $class = $type['class'];
        unset($type['class']);
        return Container::getInstance()->get($class, $params, $type);
    } elseif (is_callable($type, true)) {
        return call_user_func($type, $params);
    } elseif (is_array($type)) {
        throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
    } else {
        throw new InvalidConfigException("Unsupported configuration type: " . gettype($type));
    }
}

/**
 * Helper function to get application instance or registered application services.
 *
 * @return \Kerisy\Core\Application
 */
function app()
{
    return Container::getInstance()->getApp();
}

function service($service)
{
    return Container::getInstance()->getApp()->get($service);
}

/**
 * Helper function to get config service.
 *
 * @param string $config
 * @return \Kerisy\Core\Config
 */
function config($config)
{
    return app()->config($config);
}

/**
 * Helper function to get session service.
 *
 * @return \Kerisy\Session\Contract
 */
function session()
{
    return app()->get('session');
}

/**
 * Helper function to get auth service.
 *
 * @return \Kerisy\auth\Contract
 */
function auth()
{
    return app()->get('auth');
}

/**
 * Helper function to get current request.
 *
 * @return \Kerisy\Http\Request
 */
function request()
{
    return app()->get('request');
}

/**
 * Helper function to get current response.
 *
 * @return \Kerisy\Http\Response
 */
function response()
{
    return app()->get('response');
}


/**
 * Abort the current request.
 *
 * @param $status
 * @param string $message
 * @throws \Kerisy\Core\HttpException
 */
function abort($status, $message = null)
{
    throw new HttpException($status, $message);
}


if (!function_exists('jsonSuccess')) {

    function jsonSuccess($data, $code = '200')
    {
        $res = [
            'http_code' => (int)$code,
            'data' => $data
        ];
        return Kerisy\Support\Json::encode($res);
    }
}
if (!function_exists('jsonError')) {

    function jsonError($msg, $code = '400')
    {
        $res = [
            'http_code' => (int)$code,
            'msg' => $msg
        ];
        return Kerisy\Support\Json::encode($res);
    }
}
if (!function_exists('successFormat')) {
    function successFormat($data, $code = 200)
    {
        return [
            'http_code' => (int)$code,
            'data' => $data
        ];
    }
}
if (!function_exists('errorFormat')) {

    function errorFormat($msg, $code = 400)
    {
        return [
            'http_code' => (int)$code,
            'msg' => $msg
        ];
    }
}