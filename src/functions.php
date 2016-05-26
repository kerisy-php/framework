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
 * @deprecated
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
 * @deprecated
 */
function app()
{
    return Container::getInstance()->getApp();
}

/**
 * @param $service
 * @return mixed
 * @deprecated
 */
function service($service)
{
    return Container::getInstance()->getApp()->get($service);
}

/**
 * Helper function to get config service.
 *
 * @param string $config
 * @return \Kerisy\Core\Config
 * @deprecated
 */
function config($config)
{
    return app()->config($config);
}

/**
 * Helper function to get session service.
 *
 * @return \Kerisy\Session\Contract
 * @deprecated
 */
function session()
{
    return app()->get('session');
}

/**
 * @return null|object
 * @throws InvalidConfigException
 * @deprecated
 */
function cache()
{
    return app()->get('cache');
}

/**
 * @return null|object
 * @throws InvalidConfigException
 * @deprecated
 */
function redis()
{
    return app()->get('redis');
}

/**
 * Helper function to get auth service.
 *
 * @return \Kerisy\auth\Contract
 * @deprecated
 */
function auth()
{
    return app()->get('auth');
}

/**
 * Helper function to get current request.
 *
 * @return \Kerisy\Http\Request
 * @deprecated
 */
function request()
{
    return app()->get('request');
}

/**
 * Helper function to get current response.
 *
 * @return \Kerisy\Http\Response
 * @deprecated
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
 * @deprecated
 */
function abort($status, $message = null)
{
    throw new HttpException($status, $message);
}

if (!function_exists('jsonSuccess')) {

    /**
     * @param $data
     * @param string $code
     * @return string
     * @deprecated
     */
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

    /**
     * @param $msg
     * @param string $code
     * @return string
     * @deprecated
     */
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
    /**
     * @param $data
     * @param int $code
     * @return array
     * @deprecated
     */
    function successFormat($data, $code = 200)
    {
        return [
            'http_code' => (int)$code,
            'data' => $data
        ];
    }
}
if (!function_exists('errorFormat')) {

    /**
     * @param $msg
     * @param int $code
     * @return array
     * @deprecated
     */
    function errorFormat($msg, $code = 400)
    {
        return [
            'http_code' => (int)$code,
            'msg' => $msg
        ];
    }
}
/**
 * 对提供的数据进行urlsafe的base64编码。
 *
 * @param string $data 待编码的数据，一般为字符串
 *
 * @return string 编码后的字符串
 */
if (!function_exists('base64_urlSafeEncode')) {

    function base64_urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }
}
/**
 * 对提供的urlsafe的base64编码的数据进行解码
 *
 * @param string $data 待解码的数据，一般为字符串
 *
 * @return string 解码后的字符串
 */
if (!function_exists('base64_urlSafeDecode')) {
    function base64_urlSafeDecode($str)
    {
        $find = array('-', '_');
        $replace = array('+', '/');
        return base64_decode(str_replace($find, $replace, $str));
    }
}

/**
 * @return mixed
 * @deprecated 
 */
function ip()
{
    return request()->server['remote_addr'];
}