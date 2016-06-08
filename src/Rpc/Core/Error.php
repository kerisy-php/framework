<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/6
 */

namespace Kerisy\Rpc\Core;

class Error{

    /**
     * @var array $arrError 错误数组，先进后出
     */
    static private $arrError = array();

    /**
     * 增加错误
     * @static
     * @param mixed $err 错误对象
     */
    static public function add($err)
    {
        array_push(self::$arrError, $err);
    }

    /**
     * 清除错误数组
     * @static
     */
    static public function clear()
    {
        self::$arrError = array();
    }

    /**
     * 当前是否有错误
     * @static
     * @return bool 返回是否有错误
     */
    static public function hasError()
    {
        return (count(self::$arrError) > 0);
    }

    /**
     * 获取最后一个错误
     * @static
     * @return mixed 返回最后一个错误，同时从错误数组内移除，没有错误则返回 null
     */
    static public function lastError()
    {
//         return array_pop(Myself::$arrError);
        return end(self::$arrError);
    }

    /**
     * 获取所有错误
     * @static
     * @return array 错误数组
     */
    static public function getAll()
    {
        return self::$arrError;
    }

    /**
     * 返回错误
     * @param $info
     * @return bool
     */
    static public function returnError($info)
    {
        self::add($info);
        return false;
    }
}