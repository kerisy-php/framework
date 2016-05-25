<?php
/**
 * Created by PhpStorm.
 * User: IceQi
 * Date: 2016/3/15
 * Time: 14:59
 */

namespace Kerisy\Core\Application;

use Kerisy\Core\Dispatcher;
use Kerisy\Core\HttpException;
use Kerisy\Core\Route;
use Kerisy\Http\Request;
use Kerisy\Http\Response;
use Kerisy\Core\Application;
use Kerisy\Database\Database;

class Web extends Application
{

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

    protected function registerRoutes()
    {
        $this->dispatcher = new Dispatcher();
        $this->dispatcher->getRouter()->setConfig($this->config('routes')->all());
    }

    /**
     * Make a Request Object
     * @param array $config
     * @return Request
     * @throws \Kerisy\Core\InvalidConfigException
     */
    public function makeRequest($config = [])
    {
        /**
         * @var \Kerisy\Http\Request $request
         */
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
        $response = $this->getResponse();

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

    /**
     * Get Request Object
     * @return \Kerisy\Http\Request
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Get Response Object
     * @return \Kerisy\Http\Response
     */
    public function getResponse()
    {
        return $this->get('response');
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
            //$this->unbind($id);
            //$this->bind($id, $this->components[$id]);
            $this->clear($id);
            $this->set($id, $this->components[$id]);
        }
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function exceptionToArray(\Exception $exception)
    {
        $array = [
            'http_status' => $exception->getCode(),
            'msg' => '异常请求'
        ];

        //非生产环境输出详细Exception
        if (KERISY_ENV != 'production') {
            $array['name'] = get_class($exception);
            $array['file'] = $exception->getFile();
            $array['line'] = $exception->getLine();
            $array['message'] = $exception->getMessage();
        }

        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }

        if ($this->debug) {
            $array['trace'] = explode("\n", $exception->getTraceAsString());
            if (($prev = $exception->getPrevious()) !== null) {
                $array['previous'] = $this->exceptionToArray($prev);
            }
        }

        return $array;
    }

    protected function dispatch($request)
    {
        if (!$route = $this->dispatcher->dispatch($request)) {
            throw new \HttpException(404);
        }

        return $route;
    }

    protected function createAction(Route $route)
    {
        $class = "App\\" . ucfirst($route->getModule()) . "\\Controller\\" . ucfirst($route->getPrefix()) . "\\" . ucfirst($route->getController()) . "Controller";

        $method = $route->getAction();

        //$controller = $this->get($class);
        $controller = \Kerisy::make($class);

        $controller->callMiddleware();

        $action = [$controller, $method];

        return $action;
    }

    protected function runAction($action, $request, $response)
    {
        $data = call_user_func_array($action, [$request, $response]);

        return $data;
    }

}