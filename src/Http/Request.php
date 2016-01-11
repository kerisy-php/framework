<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Http
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Http;

use Kerisy\Contracts\Auth\Authenticatable;
use Kerisy\Core\MiddlewareTrait;
use Kerisy\Core\NotSupportedException;
use Kerisy\Core\Object;
use Kerisy\Core\ShouldBeRefreshed;

/**
 * Class Request
 *
 * @property ParamBag $params The collection of query parameters
 * @property HeaderBag $headers The collection of request headers
 * @property HeaderBag $body The collection of request body
 *
 * @property \Kerisy\Session\Session $session The session associated to the request
 *
 * @package Kerisy\Http
 */
class Request extends Object implements ShouldBeRefreshed
{
    use MiddlewareTrait;

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    public $protocol = 'HTTP/1.1';

    public $path = '/';

    /**
     * The raw content.
     *
     * @var string
     */
    public $content;

    public $queryString = '';

    public $method = 'GET';

    public $host = 'localhost';

    public $port = 8080;

    public $files = [];

    public $server = [];
    public $abort = false;

    /**
     * The key of a header field that stores the session id, or a callable that will returns the session id.
     *
     * The following is the signature of the callable:
     *
     * ```
     * string function (Request $request);
     * ```
     * @var string|callable
     */
    public $sessionKey = 'X-Session-Id';

    private $_params;

    private $_body;

    private $_headers;


    private $_route;

    public function method()
    {
        return $this->method;
    }

    /**
     * Returns whether the request method is the given $method.
     *
     * @param $method
     * @return bool
     */
    public function is($method)
    {
        return $this->method === strtoupper($method);
    }

    /**
     * Checks whether the path of the request match the given pattern.
     *
     * @param $pattern
     * @return boolean
     */
    public function match($pattern)
    {
        return preg_match($pattern, $this->path);
    }

    /**
     * Returns whether this request is secure.
     *
     * @return boolean
     */
    public function secure()
    {
        return 'HTTPS' === explode('/', $this->protocol)[0];
    }

    public function setRoute(\Kerisy\Core\Route $route)
    {
        $this->setParams($route->getParams());
        $this->_route = $route;
    }

    public function getRoute()
    {
        return $this->_route;
    }

    public function setParams($params = [])
    {
        if (empty($params)) {
            return;
        }
        if (!$params instanceof ParamBag) {
            $params = new ParamBag($params);
        }

        $this->_params = $params;
    }

    public function getParams()
    {
        if ($this->_params !== null) {
            return $this->_params;
        }

        $params = [];
        if ($this->queryString) {
            parse_str($this->queryString, $params);
        }

        return $this->_params = new ParamBag($params);
    }

    public function setHeaders($headers = [])
    {
        if (!$headers instanceof HeaderBag) {
            $headers = new HeaderBag($headers);
        }

        $this->_headers = $headers;
    }

    public function getHeaders()
    {
        if ($this->_headers === null) {
            $this->_headers = new HeaderBag();
        }

        return $this->_headers;
    }

    public function getBody()
    {
        if ($this->_body !== null) {
            return $this->_body;
        }

        if ($this->content) {
            $body = $this->parseBody($this->content);
        } else {
            $body = [];
        }

        return $this->_body = new ParamBag($body);
    }

    public function setBody($body = [])
    {
        if (!$body instanceof ParamBag) {
            $body = new ParamBag($body);
        }

        $this->_body = $body;
    }

    public function host()
    {
        $parts = explode(':', $this->headers->first('Host', 'localhost'));

        $host = $parts[0];

        if (!isset($parts[1])) {
            return $host;
        }

        $port = $parts[1];
        $secure = $this->secure();

        if ((!$secure && $port == 80) || ($secure && $port == 443)) {
            return $host;
        } else {
            return $host . ':' . $port;
        }
    }

    public function path()
    {
        return $this->path;
    }

    public function root()
    {
        return ($this->secure() ? 'https' : 'http') . '://' . $this->host();
    }

    public function url($full = true)
    {
        if ($full) {
            $params = $this->getParams();
            $params = http_build_query($params->all());
            if ($params) {
                $params = '?' . $params;
            }
        } else {
            $params = '';
        }

        return $this->root() . $this->path() . $params;
    }

    /**
     * Returns the Content-Type of the request without it's parameters.
     *
     * @return string
     */
    public function getContentType()
    {
        $contentType = $this->headers->first('Content-Type');
        if (($pos = strpos($contentType, ';')) !== false) {
            $contentType = substr($contentType, 0, $pos);
        }

        return $contentType;
    }

    /**
     * Returns parameters of the Content-Type header.
     *
     * @return array
     */
    public function getContentTypeParams()
    {
        $contentType = $this->headers->first('Content-Type');
        $contentTypeParams = [];

        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    private function parseBody($body)
    {

        $parsedBody = [];
        $contentType = $this->getContentType();

        if ($contentType == 'application/json') {
            $parsedBody = json_decode($body, true);
        } else {
            if ($contentType == 'application/x-www-form-urlencoded') {
                parse_str($body, $parsedBody);
            } elseif ($contentType == 'multipart/form-data') {
                // TODO
            } else {
                throw new NotSupportedException("The content type: '$contentType' does not supported");
            }
        }

        return $parsedBody;
    }

    /**
     * Returns whether has an input value by key.
     *
     * @param $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->params->has($key) || $this->body->has($key);
    }

    /**
     * Gets a input value by key.
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        if (($value = $this->params->get($key)) !== null) {
            return $value;
        }

        if (($value = $this->body->get($key)) !== null) {
            return $value;
        }

        return $default;
    }

    /**
     * get a $_GET value by key
     *
     * @param $_GET key
     * @param string $default
     * @return $_GET value
     */
    public function get($key, $default = null)
    {
        return $this->params->get($key, $default);
    }

    public function all()
    {
        return array_replace_recursive($this->params->all(), $this->body->all());
    }

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return array_replace_recursive($this->params->only($keys), $this->body->only($keys));
    }


    private $_session = false;

    /**
     * Sets the session of the request.
     *
     * @param $session
     */
    public function setSession($session)
    {
        $this->_session = $session;
    }

    /**
     * Returns the current session associated to the request.
     *
     * @return \Kerisy\session\Session|null
     */
    public function getSession()
    {
        if ($this->_session === false) {
            $sessionId = is_callable($this->sessionKey) ?
                call_user_func($this->sessionKey, $this) : $this->headers->first($this->sessionKey);
            if ($session = session()->get($sessionId)) {
                $this->_session = $session;
            } else {
                $this->_session = null;
            }
        }

        return $this->_session;
    }

    private $_user = false;

    /**
     * Gets or sets the authenticated user for this request.
     *
     * @param Authenticatable $user
     * @return \Kerisy\auth\Authenticatable|null
     */
    public function user(Authenticatable $user = null)
    {
        if ($user !== null) {
            $this->_user = $user;
            return;
        }

        if ($this->_user === false) {

            if (($session = $this->getSession()) && $session->id) {
                $this->_user = auth()->who($session->id);
            } else {
                $this->_user = null;
            }
        }

        return $this->_user;
    }

    /**
     * Returns the whether the request is a guest request.
     *
     * @return bool
     */
    public function guest()
    {
        return $this->user() === null;
    }

    public function to()
    {
        return $this->server_raw;
    }
}
