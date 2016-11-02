<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午6:29
 */

namespace Trendi\Http;

use swoole_http_response as SwooleHttpResponse;
use Trendi\Http\Exception\ContextErrorException;
use Trendi\Support\Exception\RuntimeExitException;
use Trendi\Support\Log;

class Response
{
    public static $response;
    private $hasEnd = 0;
    protected $headerStack = [];

    /**
     * @var \Trendi\Http\View;
     */
    public $view;

    /**
     * 初始化
     * Response constructor.
     * @param SwooleHttpResponse $response
     */
    public function __construct(SwooleHttpResponse $response)
    {
        $this->view = new View();
        self::$response = $response;
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
        return self::$response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
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
        return self::$response->rawcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 设置http code
     * @param $http_status_code
     * @return mixed
     */
    public function status($http_status_code)
    {
        return self::$response->status($http_status_code);
    }

    /**
     * 是否使用gzip 压缩
     * @param int $level
     * @return mixed
     */
    public function gzip($level = 1)
    {
        return self::$response->gzip($level);
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
        return self::$response->write($data);
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
                self::$response->header($k, $v);
            }
        }
        $data = self::$response->end($html);
        
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
                self::$response->header($k, $v);
            }
        }
        return self::$response->sendfile($filename);
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