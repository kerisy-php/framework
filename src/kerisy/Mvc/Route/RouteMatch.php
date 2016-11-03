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

use Kerisy\Mvc\Route\Base\Generator\UrlGenerator;
use Kerisy\Mvc\Route\Base\Matcher\UrlMatcher;
use Kerisy\Mvc\Route\Base\RouteCollection as BaseRouteCollection;
use Kerisy\Mvc\Route\Base\RequestContext;
use Kerisy\Http\Request;
use Kerisy\Http\Response;
use Kerisy\Mvc\Route\Exception\PageNotFoundException;
use Kerisy\Support\Arr;
use Kerisy\Support\Log;
use Kerisy\Coroutine\Event;
use Kerisy\Mvc\Route\Base;
use Kerisy\Foundation\Bootstrap\Session;

class RouteMatch
{
    protected static $instance = null;
    /**
     * 所有路由数据
     * @var array
     */
    protected static $allRoute = [];

    protected static $collectionInstance = null;
    
    protected static $middlewareConfig=[];

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
        $parameters = $matcher->match($url);
        return $parameters;
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
        $sysCacheKey = md5($routeName.serialize($params));

        $url = syscache()->get($sysCacheKey);

        if($url) return $url;

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
        Event::fire("controller.call.before", [$url]);
        Event::fire("http.controller.call.before", [$url, $request, $response]);
  
        $this->sessionStart($request, $response);

        $sysCacheKey = md5(__CLASS__.$url);

        $parameters = syscache()->get($sysCacheKey);

        if(!$parameters){
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
     * rpc 执行匹配
     *
     * @param $url
     * @param array $requestData
     * @return string
     * @throws PageNotFoundException
     */
    public function runRpc($url, $requestData = [])
    {
        Event::fire("controller.call.before", [$url]);
        Event::fire("rpc.controller.call.before", [$url, $requestData]);


        $sysCacheKey = md5($url);

        $parameters = syscache()->get($sysCacheKey);

        if(!$parameters){
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

            return $this->runBase($require, $parameters);
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
    private function runBase($require, $parameters)
    {

        if ($parameters) {
            $controller = isset($parameters['_controller']) ? $parameters['_controller'] : null;
            if ($controller) {
                $middleware = isset($parameters['_middleware']) ? $parameters['_middleware'] : null;
                if ($middleware) {
                    $midd = self::$middlewareConfig;
                    if($midd){
                        foreach ($middleware as $v){
                            if(isset($midd[$v])){
                                $class = $midd[$v];
                                $obj = new $class();
                                $rs = call_user_func_array([$obj, "perform"], $require);
                                if(!$rs) return ;
                            }
                        }
                    }
                }
                if ($controller instanceof \Closure) {
                    return call_user_func($controller, $require);
                } elseif (is_string($controller)) {
                    if (stristr($controller, "@")) {
                        list($controller, $action) = explode("@", $controller);
                        //如果是http服务器
                     
                        if(isset($require[0]) && ($require[0] instanceof Request)){
                            $obj = new $controller($require[0],$require[1]);
                            $content = call_user_func_array([$obj, $action], $require[2]);
                        }else{
                            $obj = new $controller();
                            $content = call_user_func_array([$obj, $action], $require);
                        }
           
                        Event::fire("controller.call.after", [$content]);
                        Event::fire("clear");
                        return $content;
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

    public function __destruct()
    {
    }
}