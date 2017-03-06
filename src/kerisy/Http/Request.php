<?php
/**
 * http request 类库
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

use Kerisy\Http\HttpBase\ParameterBag;
use Kerisy\Http\HttpBase\Request as BaseRequest;

class Request extends BaseRequest
{

    /**
     * 初始化
     * Request constructor.
     * @param array $swooleRequest
     */
    public function __construct($swooleRequest)
    {
        $get = isset($swooleRequest->get) ? $swooleRequest->get : [];
        $post = isset($swooleRequest->post) ? $swooleRequest->post : [];
        $attributes = [];
        $cookie = isset($swooleRequest->cookie) ? $swooleRequest->cookie : [];
        $files = isset($swooleRequest->files) ? $swooleRequest->files : [];
        $server = isset($swooleRequest->server) ? array_change_key_case($swooleRequest->server, CASE_UPPER) : [];
        if (isset($swooleRequest->header)) {
            foreach ($swooleRequest->header as $key => $value) {
                $newKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                $server[$newKey] = $value;
            }
        }

        parent::__construct($get, $post, $attributes, $cookie, $files, $server);

        // parse http body
        $contentType = $this->headers->get('CONTENT_TYPE');
        $requestMethod = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
        if (in_array($requestMethod, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $this->content = $swooleRequest->rawContent();
            $data = [];
            if (0 === strpos($contentType, 'application/x-www-form-urlencoded')) {
                parse_str($this->content, $data);
            }
            if ($data) {
                $this->request = new ParameterBag($data);
            }
        }
    }
}