<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Core
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Core;

use Kerisy\Di\Container;
use Kerisy\Log\Logger;
use Kerisy\Http\Request;
use Kerisy\Http\Response;
use Kerisy\Database\Database;

/**
 * Class Application
 *
 * @package Kerisy\Http
 */
class Application extends ServiceLocator
{
    const VERSION = '2.0.0';

    /**
     * The name for the application.
     *
     * @var string
     */
    public $name = 'Kerisy';

    /**
     * Available console commands.
     *
     * @var string[]
     */
    public $commands = [];

    public $modules = [];

    /**
     * Application component definitions.
     *
     * @var array
     */
    public $components = [];

    public $debug = true;

    /**
     * The environment that the application is running on. dev, prod or test.
     *
     * @var string
     */
    public $environment = 'development';

    public $timezone = 'UTC';

    public $runtime;

    protected $dispatcher;
    protected $bootstrapped = false;
    protected $refreshing = [];

    private $_configs;

    public function __construct()
    {
        parent::__construct($this->config('application')->all());
    }

    public function init()
    {
        if (!defined('APPLICATION_PATH') || !file_exists(APPLICATION_PATH)) {
            throw new InvalidParamException("The param: 'APPLICATION_PATH' is invalid");
        }

        $this->components = array_merge($this->defaultComponents(), $this->config('components')->all());

        Container::getInstance()->setApp($this);
    }

    public function bootstrap()
    {
        if (!$this->bootstrapped) {
            $this->initializeConfig();
            $this->registerComponents();
            $this->registerRoutes();
            //$this->registerEntities();
            $this->bootstrapped = true;

            $this->get('log')->info('application started');
        }

        return $this;
    }

    protected function initializeConfig()
    {
        date_default_timezone_set($this->timezone);
    }

    /*
        protected function registerEntities()
        {
            $config = $this->config('database')->all();

            return new Database($config['connections'][$config['default']]);
        }
    */

    protected function registerComponents()
    {
        foreach ($this->components as $id => $definition) {
            $this->bind($id, $definition);
        }

        foreach ($this->components as $id => $_) {
            if ($this->get($id) instanceof ShouldBeRefreshed) {
                $this->refreshing[$id] = true;
            }
        }
    }

    public function config($config_group)
    {
        if (!isset($this->_configs[$config_group])) {
            $config = new Config($config_group);
            $this->_configs[$config_group] = $config;
        }

        return $this->_configs[$config_group];
    }

    protected function registerRoutes()
    {
        $this->dispatcher = new Dispatcher();
        $this->dispatcher->getRouter()->setConfig($this->config('routes')->all());
    }

    public function defaultComponents()
    {
        return [
            'errorHandler' => [
                'class' => ErrorHandler::class,
            ],
            'log' => [
                'class' => Logger::class,
            ],
            'request' => [
                'class' => Request::class,
            ],
            'response' => [
                'class' => Response::class,
            ],
        ];
    }

    public function makeRequest($config = [])
    {
        $request = $this->get('request');

        foreach ($config as $name => $value) {
            $request->$name = $value;
        }

        return $request;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function handleRequest($request)
    {
        /** @var Response $response */
        $response = $this->get('response');

        try {
            $this->exec($request, $response);
        } catch (\Exception $e) {
            $response->data = $e;
            $this->get('errorHandler')->handleException($e);
        }

        try {
            $response->callMiddleware();
        } catch (\Exception $e) {
            $response->data = $e;
        }

        $this->formatException($response->data, $response);

        $response->prepare();
        $this->refreshComponents();

        return $response;
    }

    protected function formatException($e, $response)
    {
        if (!$response->data instanceof \Exception) {
            return;
        }

        if ($e instanceof HttpException) {
            $response->status($e->statusCode);
            $response->data = $this->exceptionToArray($e);
        } else {
            if ($this->environment === 'test') {
                throw $e;
            }

            $response->status(500);
            $response->data = $this->exceptionToArray($e);
        }
    }

    protected function exec(Request $request, Response $response)
    {
        $route = $this->dispatch($request);

        $request->setRoute($route);

        $action = $this->createAction($route);

        // 中止继续访问
        if ($request->abort == true) {
            return;
        }

        $request->callMiddleware();

        $response->setPrefix($route->getPrefix());

        $data = $this->runAction($action, $request, $response);

        if (!$data instanceof Response && $data !== null) {
            $response->with($data);
        }
    }

    protected function refreshComponents()
    {
        foreach ($this->refreshing as $id => $_) {
            $this->unbind($id);
            $this->bind($id, $this->components[$id]);
        }
    }

    public function handleConsole($input, $output)
    {
        $app = new \Kerisy\Core\Console\Application([
            'name' => 'Kerisy Command Runner',
            'version' => self::VERSION,
            'kerisy' => $this,
        ]);

        $commands = array_merge($this->commands, [
            'Kerisy\Console\ServerCommand',
        ]);

        foreach ($commands as $command) {
            $app->add(make(['class' => $command, 'kerisy' => $this]));
        }

        return $app->run($input, $output);
    }

    protected function exceptionToArray(\Exception $exception)
    {
        $array = [
            'name' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'http_status' => $exception->getCode(),
            'msg' => '异常请求'
        ];

        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }

        if ($this->debug) {
            $array['file'] = $exception->getFile();
            $array['line'] = $exception->getLine();
            $array['trace'] = explode("\n", $exception->getTraceAsString());
        }

        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->exceptionToArray($prev);
        }

        return $array;
    }

    protected function dispatch($request)
    {
        if (!$route = $this->dispatcher->dispatch($request)) {
            throw new HttpException(404);
        }

        return $route;
    }

    protected function createAction(Route $route)
    {
        $class = "App\\" . ucfirst($route->getModule()) . "\\Controller\\" . ucfirst($route->getPrefix()) . "\\" . ucfirst($route->getController()) . "Controller";
        $method = $route->getAction();

        $controller = $this->get($class);
        $controller->callMiddleware();

        $action = [$controller, $method];

        return $action;
    }

    protected function runAction($action, $request, $response)
    {
        $data = call_user_func_array($action, [$request, $response]);

        return $data;
    }

    /**
     * Shutdown the application.
     */
    public function shutdown()
    {

    }
}
