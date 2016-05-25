<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author             Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package            kerisy/framework
 * @subpackage         Server
 * @since              2015/11/11
 * @version            2.0.0
 */

namespace Kerisy\Server;

use Kerisy;

/**
 * A Swoole based server implementation.
 *
 * @package Kerisy\Server
 */
class Swoole extends Base
{
    public $alias_name = '';

    /**
     * The number of requests each process should execute before respawning, This can be useful to work around
     * with possible memory leaks.
     *
     * @var int
     */
    public $maxRequests = 65535;

    /**
     * The number of workers should be started to serve requests.
     *
     * @var int
     */
    public $numWorkers;

    /**
     * Detach the server process and run as daemon.
     *
     * @var bool
     */
    public $asDaemon = FALSE;

    /**
     * Specifies the path where logs should be stored in.
     *
     * @var string
     */
    public $logFile;

    /**
     * Normalized Swoole Http Server Config
     *
     * @return array
     */
    private function normalizedConfig()
    {
        $config = [];

        $config['max_request'] = $this->maxRequests;
        $config['daemonize'] = $this->asDaemon;

        if ($this->numWorkers) {
            $config['worker_num'] = $this->numWorkers;
        }

        if ($this->logFile) {
            $config['log_file'] = $this->logFile;
        }

        return $config;
    }

    /**
     * Create Server
     *
     * @return \swoole_http_server
     */
    private function createServer()
    {
        $server = new \swoole_http_server($this->host, $this->port);

        $server->on('start', [$this, 'onServerStart']);
        $server->on('shutdown', [$this, 'onServerStop']);

        $server->on('managerStart', [$this, 'onManagerStart']);

        $server->on('workerStart', [$this, 'onWorkerStart']);
        $server->on('workerStop', [$this, 'onWorkerStop']);

        $server->on('request', [$this, 'onRequest']);

        if (method_exists($this, 'onOpen')) {
            $server->on('open', [$this, 'onOpen']);
        }
        if (method_exists($this, 'onClose')) {
            $server->on('close', [$this, 'onClose']);
        }

        if (method_exists($this, 'onWsHandshake')) {
            $server->on('handshake', [$this, 'onWsHandshake']);
        }
        if (method_exists($this, 'onWsMessage')) {
            $server->on('message', [$this, 'onWsMessage']);
        }

        if (method_exists($this, 'onTask')) {
            $server->on('task', [$this, 'onTask']);
        }
        if (method_exists($this, 'onFinish')) {
            $server->on('finish', [$this, 'onFinish']);
        }

        $server->set($this->normalizedConfig());

        return $server;
    }

    /**
     * Listen Server Start Event
     *
     * @param \swoole_http_server $server
     */
    public function onServerStart($server)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': master');
        if ($this->pidFile) {
            file_put_contents($this->pidFile, $server->master_pid);
        }
    }

    /**
     * Listen Manager Start Event
     *
     * @param \swoole_http_server $server
     */
    public function onManagerStart($server)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': manager');
    }

    public function onServerStop()
    {
        if ($this->pidFile) {
            unlink($this->pidFile);
        }
    }

    /**
     * Listen Worker Start Event
     */
    public function onWorkerStart()
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': worker');
        $this->startApp();
    }

    public function onWorkerStop()
    {
        $this->stopApp();
    }

    public function onTask()
    {

    }

    public function onFinish()
    {

    }

    /**
     * Prepare Request
     *
     * @param \swoole_http_request $request
     * @return Kerisy\Http\Request
     */
    protected function prepareRequest($request)
    {
        $hosts = explode(':', $request->header['host']);
        $host = $hosts[0];
        $port = isset($hosts[1]) ? (int)$hosts[1] : 80;

        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];

        $config = [
            'protocol' => $request->server['server_protocol'],
            'host' => $host,
            'port' => $port,
            'method' => $request->server['request_method'],
            'path' => $request->server['request_uri'],
            'headers' => $request->header,
            'cookie' => $cookie,
            'params' => array_merge($get, $post),
            'content' => $request->rawcontent(),
            'files' => $files,
            'server' => $request->server
        ];

        return Kerisy::$app->makeRequest($config);
    }

    /**
     * Listen Request Event
     *
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function onRequest($request, $response)
    {
        $res = $this->handleRequest($this->prepareRequest($request));

        $content = $res->content();

        foreach ($res->headers->all() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }
        $response->header('Content-Length', strlen($content));
        if ($res->getCookies()) {
            foreach ($res->getCookies() as list($key, $value, $ttl, $path, $domain, $secure, $httponly)) {
                $response->cookie($key, $value, $ttl, $path = '/', $domain, $secure, $httponly);
            }
        }
        $response->status($res->statusCode);
        $response->end($content);
    }

    /**
     * Server Start
     */
    public function run()
    {
        $server = $this->createServer();
        $server->start();
    }

    public function setAliasName($alias_name = '')
    {
        $this->name .= ' '.$alias_name ?: 'server';
    }
}
