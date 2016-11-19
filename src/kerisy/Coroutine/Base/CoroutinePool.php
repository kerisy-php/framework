<?php
/**
 * 池子处理
 *
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */


namespace Kerisy\Coroutine\Base;


abstract class CoroutinePool
{
    const MAX_TOKEN = 1000;
    protected static $commands;
    protected static $pool;
    protected static $callBacks;
    protected static $token = 0;
    //避免爆发连接的锁
    protected static $prepareLock = [];

    public function __construct()
    {
        self::$callBacks = new \SplFixedArray(self::MAX_TOKEN);
        self::$commands = [];
        self::$pool = [];
    }

    abstract protected function execute($data);


    public function perform($callback, $data)
    {
        $data['token'] = $this->addTokenCallback($callback);
        call_user_func([$this, 'execute'], $data);
    }

    public function addTokenCallback($callback)
    {
        $token = self::$token;
        self::$callBacks[$token] = $callback;
        self::$token++;
        if (self::$token >= self::MAX_TOKEN) {
            self::$token = 0;
        }
        return $token;
    }

    /**
     * 分发消息
     * @param $data
     */
    public function distribute($data)
    {
        $callback = self::$callBacks[$data['token']];
        unset(self::$callBacks[$data['token']]);
        if ($callback != null) {
            call_user_func_array($callback, ['data' => $data['result']]);
        }
    }


    /**
     * @param $client
     */
    public function pushToPool($client, $connType = 0)
    {
        self::$prepareLock[$connType] = false;
        self::$pool[$connType][] = $client;
        if (isset(self::$commands[$connType]) && self::$commands[$connType]) {//有残留的任务
            $command = array_pop(self::$commands[$connType]);
            $this->execute($command);
        }
    }

    /**
     *
     */
    public function freeCallback()
    {
        unset(self::$callBacks);
    }
}