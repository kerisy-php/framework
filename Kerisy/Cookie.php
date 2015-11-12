<?php

/**
 * Created by PhpStorm.
 * User: yanfei
 * Date: 2015/7/24
 * Time: 11:48
 */
class Kerisy_Cookie
{
    private $prefix = '';
    private $domain = '.putao.com';
    private $expire = 2592000;//30 day

    public function __construct()
    {
        $cookie = Kerisy::config()->get()->cookie;
        $this->prefix = $cookie['prefix'];
        $this->domain = $cookie['domain'];
        $this->expire = $cookie['expire'];
    }

    public function set($key, $value)
    {
        return setcookie($key, $value, time() + $this->expire, '/', $this->domain);
    }

    public function get($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    public function destroy($key)
    {
        return setcookie($key, '', time() - 3600, '/', $this->domain);
    }

    public function batchSet($item)
    {
        foreach ($item as $key => $val) {
            setcookie($key, $val, time() + $this->expire, '/', $this->domain);
        }
        return true;
    }

    public function batchDestroy($item)
    {
        foreach ($item as $_item) {
            setcookie($_item, '', time() - 3600, '/', $this->domain);
        }
        return true;
    }

} 