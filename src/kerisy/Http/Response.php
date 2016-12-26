<?php
/**
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

use swoole_http_response as SwooleHttpResponse;
use Kerisy\Http\Exception\ContextErrorException;
use Kerisy\Support\Exception\RuntimeExitException;
use Kerisy\Support\Log;

class Response
{
    public $response;
    public $view;
    private $hasEnd = 0;
    protected $headerStack = [];

    /**
     * 初始化
     * Response constructor.
     * @param SwooleHttpResponse $response
     */
    public function __construct(SwooleHttpResponse $response)
    {
        $this->response = $response;
        $this->view = new AssignData();
    }

    public function setHasEnd($hasEnd)
    {
        $this->hasEnd = $hasEnd;
    }
    
    /**
     * 设置cookie
     * @param $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return mixed
     */
    public function cookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        return $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 设置cookie
     *
     * @param $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return mixed
     */
    public function rawcookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        return $this->response->rawcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 设置http code
     * @param $http_status_code
     * @return mixed
     */
    public function status($http_status_code)
    {
        return $this->response->status($http_status_code);
    }

    /**
     * 是否使用gzip 压缩
     * @param int $level
     * @return mixed
     */
    public function gzip($level = 1)
    {
        return $this->response->gzip($level);
    }

    /**
     * header
     * @param $key
     * @param $value
     */
    public function header($key, $value)
    {
        $this->headerStack[$key] = $value;
    }

    /**
     * write
     *
     * @param $data
     * @return mixed
     */
    public function write($data)
    {
        return $this->response->write($data);
    }

    /**
     * 输出
     * @param string $html
     * @return mixed
     * @throws ContextErrorException
     */
    public function end($html = '')
    {
        if ($this->hasEnd) {
            return Log::sysinfo("http has send");
        }
        
        $this->hasEnd = 1;
        if ($this->headerStack) {
            foreach ($this->headerStack as $k => $v) {
                $this->response->header($k, $v);
            }
        }
        $data = $this->response->end($html);
        
        return $data;
    }

    /**
     * 输出file
     *
     * @param $filename
     * @return mixed
     */
    public function sendfile($filename)
    {
        if ($this->headerStack) {
            foreach ($this->headerStack as $k => $v) {
                $this->response->header($k, $v);
            }
        }
        return $this->response->sendfile($filename);
    }

    /**
     * 跳转
     * @param $url
     * @return mixed
     * @throws ContextErrorException
     */
    public function redirect($url)
    {
        $this->header("Location", $url);
        $this->status(302);
        $this->end('');
        //抛异常中断执行
        throw new RuntimeExitException('redirect->'. $url);
    }

}