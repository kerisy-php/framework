<?php
/**
 *  session handle
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

namespace Kerisy\Http;

use Kerisy\Server\Facade\Context;
use Kerisy\Support\Log;
use Kerisy\Support\Serialization\Serialization;

class Session
{
    protected $sid = null;

    /**
     * @var \Kerisy\Cache\Adapter\RedisCache
     */
    private $server = null;
    private $config = [];
    private $request = null;


    public function __construct($config=[], $server=null)
    {
        $this->config = $config;
        $this->server = $server;
        $this->request = Context::request();
    }
    
    public function start($request, $response)
    {
        if(!$this->config||!$this->server) return ;
        
        $config = $this->config;

        $sid = null;
        $lifetime = 0;

        if (isset($config['cache_expire'])) {
            $expire = $config['cache_expire'];
            $lifetime = time()+$expire * 2;
        } else {
            $expire = 60 * 60;
        }

        $path = !isset($config['path']) ? '/' : $config['path'];
        $domain = !isset($config['domain']) ? '' : $config['domain'];
        $secure = !isset($config['secure']) ? false : $config['secure'];
        $httponly = !isset($config['httponly']) ? true : $config['httponly'];

        $sessionName = empty($config['name']) ? 'TSESSIONID' : $config['name'];

        if ($request->query->get($sessionName)) {
            $sid = $request->query->get($sessionName);
        } elseif ($request->request->get($sessionName)) {
            $sid = $request->request->get($sessionName);
        } elseif ($request->cookies->get($sessionName)) {
            $sid = $request->cookies->get($sessionName);
        } else {
            $sid = sha1($request->headers->get('user-agent') . $request->server->get('remote_addr') . uniqid(posix_getpid(), true));
        }
        $response->rawcookie($sessionName, $sid, $lifetime, $path, $domain, $secure, $httponly);
        $this->sid = $sid;
        $this->set("kerisy_heart", 1);
        $this->server->expire($sid, $expire);
    }

    /**
     * 获取session
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $sid = $this->getSid();
        $result = $this->server->hget($sid, $key);
        if(!$result) return $result;
        return Serialization::get()->xtrans($result);
    }

    /**
     * 设置session
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $sid = $this->getSid();
        $value = Serialization::get()->trans($value);
        $this->server->hset($sid, $key, $value);
    }

    /**
     * 获取id
     * @return null
     */
    public function getSid()
    {
        $request = $this->request;
        $config = $this->config;
        $sessionName = empty($config['name']) ? 'KSESSIONID' : $config['name'];
        if ($request->query->get($sessionName)) {
            $sid = $request->query->get($sessionName);
        } elseif ($request->request->get($sessionName)) {
            $sid = $request->request->get($sessionName);
        } elseif ($request->cookies->get($sessionName)) {
            $sid = $request->cookies->get($sessionName);
        } else {
            $sid = sha1($request->headers->get('user-agent') . $request->server->get('remote_addr') . uniqid(posix_getpid(), true));
        }
        return $sid;
    }

    /**
     *  删除key
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        $sid = $this->getSid();
        return $this->server->hdel($sid, $key);
    }

    /**
     * 清空
     * @return mixed
     */
    public function clear()
    {
        $sid = $this->getSid();
        return $this->server->del($sid);
    }
}