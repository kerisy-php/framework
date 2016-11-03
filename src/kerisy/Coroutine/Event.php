<?php
/**
 *  event
 *
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 上午9:13
 */

namespace Kerisy\Coroutine;


class Event
{
    /**
     *  event 保存静态变量
     *
     * @var array
     */
    private static $evtMap = [];

    /**
     * 普通事物
     */
    const NORMAL_EVENT = 1;

    /**
     * 只执行一次的事物
     */
    const ONCE_EVENT = 2;

    /**
     * 清空
     */
    public static function clear()
    {
        self::$evtMap = [];
    }

    /**
     * register
     * @param $evtName
     */
    public static function register($evtName)
    {
        if (!isset(self::$evtMap[$evtName])) {
            self::$evtMap[$evtName] = [];
        }
    }

    /**
     * unregister
     *
     * @param $evtName
     */
    public static function unregister($evtName)
    {
        if (isset(self::$evtMap[$evtName])) {
            unset(self::$evtMap[$evtName]);
        }
    }

    /**
     *  一次处理
     *
     * @param $evtName
     * @param $callback
     */
    public static function once($evtName, $callback)
    {
        self::register($evtName);

        self::$evtMap[$evtName][] = [
            'callback' => $callback,
            'evtType' => Event::ONCE_EVENT,
        ];
    }

    /**
     *  bing event
     *
     * @param $evtName
     * @param $callback
     */
    public static function bind($evtName, $callback)
    {
        self::register($evtName);

        self::$evtMap[$evtName][] = [
            'callback' => $callback,
            'evtType' => Event::NORMAL_EVENT,
        ];
    }

    /**
     * 解绑
     * @param $evtName
     * @param $callback
     * @return bool
     */
    public static function unbind($evtName, $callback)
    {
        if (!isset(self::$evtMap[$evtName]) || !self::$evtMap[$evtName]) {
            return false;
        }

        foreach (self::$evtMap[$evtName] as $key => $evt) {
            $cb = $evt['callback'];
            if ($cb == $callback) {
                unset(self::$evtMap[$evtName][$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * 执行
     * @param $evtName
     * @param null $args
     * @param bool $loop
     */
    public static function fire($evtName, $args = null, $loop = true)
    {
        if (isset(self::$evtMap[$evtName]) && self::$evtMap[$evtName]) {
            self::fireEvents($evtName, $args, $loop);
        }
    }

    /**
     * 执行
     * @param $evtName
     * @param null $args
     * @param bool $loop
     * @return array
     */
    private static function fireEvents($evtName, $args = null, $loop = true)
    {
        $result = [];
        foreach (self::$evtMap[$evtName] as $key => $evt) {
            $callback = $evt['callback'];
            $evtType = $evt['evtType'];
            $result[] = call_user_func($callback, $args);

            if (Event::ONCE_EVENT === $evtType) {
                unset(self::$evtMap[$evtName][$key]);
            }

            if (false === $loop) {
                break;
            }
        }
        return $result;
    }
}