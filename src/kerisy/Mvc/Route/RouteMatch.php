<?php
/**
 * 匹配route
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

namespace Kerisy\Mvc\Route;

use Kerisy\Coroutine\Base\CoroutineTask;
use Kerisy\Coroutine\Event;
use Kerisy\Foundation\Bootstrap\Session;
use Kerisy\Http\Request;
use Kerisy\Http\Response;
use Kerisy\Mvc\Route\Base;
use Kerisy\Mvc\Route\Base\Generator\UrlGenerator;
use Kerisy\Mvc\Route\Base\Matcher\UrlMatcher;
use Kerisy\Mvc\Route\Base\RequestContext;
use Kerisy\Mvc\Route\Base\RouteCollection as BaseRouteCollection;
use Kerisy\Mvc\Route\Exception\PageNotFoundException;
use Kerisy\Support\Arr;

class RouteMatch
{
    protected static $instance = null;
    /**
     * 所有路由数据
     * @var array
     */
    protected static $allRoute = [];

    protected static $collectionInstance = null;

    protected static $middlewareConfig = [];

    protected static $dispatch = [];

    public static function getDispatch()
    {
        return self::$dispatch;
    }

    /**
     *  instance
     * @return \Kerisy\Mvc\Route\RouteMatch
     */
    public static function getInstance()
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self();
    }

    public function setMiddlewareConfig($middlewareConfig)
    {
        self::$middlewareConfig = $middlewareConfig;
    }

    protected function getAllGroupRoute()
    {
        if (self::$allRoute) return self::$allRoute;
        return self::$allRoute = RouteGroup::getResult();
    }


    protected function getRootCollection()
    {
        if (self::$collectionInstance) return self::$collectionInstance;
        $rootCollection = new BaseRouteCollection();
        $allRoute = $this->getAllGroupRoute();
        if ($allRoute) {
            foreach ($allRoute as $v) {
                $rootCollection->addCollection($v);
            }
        }

        //get Single route collection
        $single = RouteBase::getResult(RouteBase::DEFAULTGROUP_KEY);

        if ($single) {
            foreach ($single as $k => $v) {
                $rootCollection->add($k, $v);
            }
        }

        return self::$collectionInstance = $rootCollection;
    }

    /**
     * 修复首页匹配问题
     * @param $url
     * @return mixed
     */
    protected function groupFilter($url)
    {
        $groupPrefixs = RouteGroup::getGroupPrefixs();
        if (!$groupPrefixs) return $url;
        $urlTmp = ltrim($url, "/");

        if (in_array($urlTmp, $groupPrefixs)) {
            return $url . "/";
        }
        return $url;
    }

    /**
     * 获取匹配数据
     * @param $url
     * @return array
     */
    public function match($url)
    {
        $rootCollection = $this->getRootCollection();
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());
        $matcher = new UrlMatcher($rootCollection, $context);
        $url = $this->groupFilter($url);
        $parameters = $matcher->match($url);
        $this->setDispatch($parameters);
        return $parameters;
    }

    /**
     * 获取分派信息
     *
     * @param $parameters
     * @return bool
     */
    public function setDispatch($match)
    {
        if(isset($match['_route']) && $match['_route']) {
            $routeName = $match['_route'];
            $arrTmp = explode("@", $routeName);
            self::$dispatch['groupName'] = current($arrTmp);
            self::$dispatch['routeName'] = end($arrTmp);
        }

        if(isset($match['_controller']) && $match['_controller']) {
            $controller = $match['_controller'];
            self::$dispatch['controller'] = current(explode("@", $controller));
        }
    }

    /**
     * 简化url调用
     *
     * @param $routeName
     * @param array $params
     * @param string $groupName
     * @return mixed
     */
    public function simpleUrl($routeName, $params = [], $groupName='')
    {
        if($groupName){
            $routeName = $groupName."@".$routeName;
        }else{
            if(isset(self::$dispatch['groupName']) && self::$dispatch['groupName']){
                $routeName = self::$dispatch['groupName']."@".$routeName;
            }
        }
        return $this->url($routeName, $params);
    }


    /**
     * 根据路由名称获取url
     *
     * @param $routeName
     * @param array $params
     * @return string
     */
    public function url($routeName, $params = [])
    {
        if (!$routeName) return "";

        $sysCacheKey = md5($routeName . serialize($params));

        $url = syscache()->get($sysCacheKey);

        if ($url) return $url;

        $rootCollection = $this->getRootCollection();
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());
        $generator = new UrlGenerator($rootCollection, $context);
        $url = $generator->generate($routeName, $params);

        syscache()->set($sysCacheKey, $url, 3600);

        return $url;
    }

    /**
     * http 执行匹配
     *
     * @param $url
     * @return mixed
     * @throws PageNotFoundException
     */
    public function run($url, Request $request, Response $response)
    {
        $this->sessionStart($request, $response);

        $sysCacheKey = md5(__CLASS__ . $url);

        $parameters = syscache()->get($sysCacheKey);

        if (!$parameters) {
            $parameters = $this->match($url);
            syscache()->set($sysCacheKey, $parameters, 3600);
        }

        if ($parameters) {
            $secondReq = [];
            foreach ($parameters as $k => $v) {
                if (substr($k, 0, 1) != '_') {
                    $secondReq[$k] = $v;
                }
            }

            $request->overrideGlobals();

            $require = [$request, $response, $secondReq];

            return $this->runBase($require, $parameters);
        }
    }

    /**
     * session start
     *
     * @param $request
     * @param $response
     */
    protected function sessionStart($request, $response)
    {
        $session = new Session();
        $session->start($request, $response);
    }


    /**
     * socket 执行匹配
     *
     * @param $url
     * @param array $requestData
     * @param server
     * @return string
     * @throws PageNotFoundException
     */
    public function runSocket($url, $requestData = [], $serv, $fd)
    {
        $sysCacheKey = md5($url);

        $parameters = syscache()->get($sysCacheKey);

        if (!$parameters) {
            $parameters = $this->match($url);
            syscache()->set($sysCacheKey, $parameters, 3600);
        }

        if ($parameters) {
            $require = [];
            foreach ($parameters as $k => $v) {
                if (substr($k, 0, 1) != '_') {
                    $require[$k] = $v;
                }
            }

            if ($requestData) $require = Arr::merge($require, $requestData);

            return $this->runBase($require, $parameters, [$serv, $fd]);
        }
        return "";
    }

    /**
     * 基础执行匹配
     *
     * @param $require
     * @param $parameters
     * @return mixed
     * @throws PageNotFoundException
     */
    private function runBase($require, $parameters, $otherData = null)
    {
        if ($parameters) {
            $controller = isset($parameters['_controller']) ? $parameters['_controller'] : null;
            if ($controller) {
                $middleware = isset($parameters['_middleware']) ? $parameters['_middleware'] : null;

                $isClosure = 0;
                if ($controller instanceof \Closure) {
                    $isClosure = 1;
                }

                if ($isClosure) {
                    call_user_func($controller, $require);
                } elseif (is_string($controller)) {
                    if (stristr($controller, "@")) {
                        list($controller, $action) = explode("@", $controller);
                        //如果是http服务器
                        if (isset($require[0]) && ($require[0] instanceof Request)) {
                            $obj = new $controller($require[0], $require[1]);
                            $check = $this->todoMiddleWare($obj, $action, $middleware, $require);
                            if (!$check) return;
                            $realParams = $this->callUserFuncArrayRealParams($controller, $action, $require[2]);
                            $content = call_user_func_array([$obj, $action], $realParams);
                        } else {
                            //tcp
                            list($serv, $fd) = $otherData;
                            $obj = new $controller($serv, $fd);
                            $postData = $otherData + $require;
                            $check = $this->todoMiddleWare($obj, $action, $middleware, $postData);
                            if (!$check) return;
                            $realParams = $this->callUserFuncArrayRealParams($controller, $action, $require);
                            $content = call_user_func_array([$obj, $action], $realParams);
                        }
                        if ($content instanceof \Generator) {
                            $task = new CoroutineTask($content);
                            $task->work($task->getRoutine());
                            unset($task);
                        }
                        Event::fire("clear");
                    } else {
                        throw new PageNotFoundException("page not found!");
                    }
                } else {
                    throw new PageNotFoundException("page not found!");
                }
            } else {
                throw new PageNotFoundException("page not found!");
            }
        }
    }

    protected function callUserFuncArrayRealParams($class, $function, $params)
    {
        $reflect = new \ReflectionMethod($class, $function);
        $real_params = array();
        foreach ($reflect->getParameters() as $i => $param)
        {
            $pname = $param->getName();
            if (array_key_exists($pname, $params))
            {
                $real_params[] = $params[$pname];
            }
        }

        return $real_params;
    }

    protected function todoMiddleWare($obj, $action, $middleware, $require)
    {
        $whiteAction = [];
        if (method_exists($obj, "whiteActions")) {
            $whiteAction = $obj->whiteActions();
        }

        if (isset($whiteAction["*"])) {
            if (in_array($action, $whiteAction["*"])) return true;
        }

        $check = $this->runMiddleware($middleware, $require, $action, $whiteAction);
        if (!$check) return false;
        return true;
    }

    /**
     * 执行中间件
     * @param $middleware
     * @param $require
     * @return bool
     */
    protected function runMiddleware($middleware, $require, $action, $whiteAction)
    {
        if ($middleware) {
            $middleware = is_string($middleware) ? [$middleware] : $middleware;
            $midd = self::$middlewareConfig;
            if ($midd) {
                foreach ($middleware as $v) {
                    if($whiteAction){
                        if (isset($whiteAction[$v])) {
                            if (in_array($action, $whiteAction[$v])) continue;
                        }else{
                            if (in_array($action, $whiteAction)) continue;
                        }
                    }

                    if (isset($midd[$v])) {
                        $class = $midd[$v];
                        $obj = new $class();
                        $rs = call_user_func_array([$obj, "perform"], $require);
                        if (!$rs) return false;
                    }
                }
            }
        }
        return true;
    }

    public function __destruct()
    {
    }
}