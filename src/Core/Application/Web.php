<?php
/**
 * Created by PhpStorm.
 * User: IceQi
 * Date: 2016/3/15
 * Time: 14:59
 */

namespace Kerisy\Core\Application;

use Kerisy\Core\Dispatcher;
use Kerisy\Core\Route;
use Kerisy\Di\Container;
use Kerisy\Log\Logger;
use Kerisy\Http\Request;
use Kerisy\Http\Response;
use Kerisy\Database\Database;
use Kerisy\Core\Application;
use Kerisy\Core\Hook;
use Kerisy\Lang\Lang;

class Web extends Application
{

    public function __construct()
    {
        //添加开始时间钩子
        \Kerisy\Core\Hook::add("server_start", function () {
            $startTime = \Kerisy\Tool\RunTime::setStartTime();
            return $startTime;
        });
        
        parent::__construct();
    }

    public function bootstrap()
    {
        if (!$this->bootstrapped) {

            $this->initializeConfig();
            $this->registerComponents();
            $this->registerRoutes();
            //$this->registerEntities();
            $this->registerLang();
            $this->bootstrapped = true;

            $this->get('log')->info('application started');
        }

        return $this;
    }
    
    

    protected function registerLang()
    {
        new Lang();
    }

    protected function prepareRequest($request)
    {
//        print_r($_SERVER);
        $port = 80;
        $hosts = explode(':' , $_SERVER['HTTP_HOST']);

        if ( count($hosts) > 1 ) {
            $port = $hosts[1];
        }
        $host = $hosts[0];
        $params = [];

        //输入过滤
        !empty($_POST) && self::addS($_POST);
        !empty($_GET) && self::addS($_GET);
        !empty($_COOKIE) && self::addS($_COOKIE);
        !empty($_FILES) && self::addS($_FILES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST && $params = $_POST;
        }

        $urlParse = parse_url($_SERVER['REQUEST_URI']);

        $_GET && $params += $_GET;

        $_FILES && $params += $_FILES;

        $config = [
            'protocol' => $_SERVER['SERVER_PROTOCOL'] ,
            'host'     => $host ,
            'port'     => $port ,
            'method'   => $_SERVER['REQUEST_METHOD'] ,
            'path'     => str_replace($_SERVER['SCRIPT_NAME'],'',$urlParse['path']) ,
//            'headers'  => $request->header ,
//            'headers' =>"",
            'params'   => $params,
//            'content'  => $request->rawcontent() ,
            'content'  => "" ,
//            'server'   => $request->server
            'server'   => $_SERVER
        ];
        if ($_FILES) {
            $config['files'] = $_FILES;
        }
        if ($_COOKIE) {
            $config['cookie'] = $_COOKIE;
        }

        return app()->makeRequest($config);
    }

    public static function addS(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $array[$key] = addslashes($value);
                } else {
                    self::addS($array[$key]);
                }
            }
        } else {
            $array = addslashes($array);
        }
    }

    public function webHandle()
    {
        Hook::fire("server_start");

        $this->bootstrap();
        $request = $this->get('request');
        $request = $this->prepareRequest($request);

        $response = $this->get('response');
//        print_r($request);
        ob_start();
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
//        $content = is_string($response->data) ? $response->data : Json::encode($response->data);
//
//        if (!is_string($response->data)) {
//            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
//        }
        $content = $response->content();

        if(!headers_sent()){
            foreach ( $response->headers->all() as $name => $values ) {
                $name = str_replace(' ' , '-' , ucwords(str_replace('-' , ' ' , $name)));
                foreach ( $values as $value ) {
                    $header = "{$name}:{$value}";
                    if($header){
                        header($header);
                    }
                }
            }
        }

        $ob_content = ob_get_contents();

        ob_end_clean();
        $content = $ob_content?$ob_content:$content;
        echo $content;
        
        Hook::fire("server_end",[$request,$response]);
        
        return $content;
    }

    protected function registerRoutes()
    {
        $this->dispatcher = new Dispatcher();
        $this->dispatcher->getRouter()->setConfig($this->config('routes')->all());
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

        Hook::fire("action_pre",[$request,$response]);
        
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
        
        if(!class_exists($class)){
           $prefixs = $route->getALLPrefix();
            if($prefixs){
                foreach ($prefixs as $v){
                    $class = "App\\" . ucfirst($route->getModule()) . "\\Controller\\" . ucfirst($v) . "\\" . ucfirst($route->getController()) . "Controller";
                    if(class_exists($class)){
                        $route->setPrefix($v);
                        break;
                    }
                }
            }
        }
        
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

}