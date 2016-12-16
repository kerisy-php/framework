<?php
/**
 * User: Peter Wang
 * Date: 16/12/16
 * Time: 下午1:51
 */

namespace Kerisy\WebSocket;


use Kerisy\Core\Application as CoreApplication;
use stdClass;
use Kerisy\Core\Router;
use Kerisy\Core\Route;

class Application extends CoreApplication
{

    public function __construct()
    {
        parent::__construct($this->config('application')->all());
    }

    public function start($data, $server, $fd)
    {
        list($path, $params) = json_decode($data, true);
        $requestData = array();
        $requestData['path'] = $path;
        $requestData['params'] = $params;
        $this->match($path, $params, $server, $fd);
    }

    protected function match($path, $params, $server, $fd)
    {
        $objRouter = Router::getInstance();
        $request = new stdClass();
        $request->path = $path;
        $request->host = "";
        $route = $objRouter->routing($request);
        list($controller, $action) = $this->createAction($route);
        $obj = new $controller($server, $fd);
        $obj->callMiddleware();
        call_user_func_array([$obj, $action], $params);
    }

    /**
     * 匹配
     * @param Route $route
     * @return array
     */
    protected function createAction(Route $route)
    {
        $class = "\\App\\" . ucfirst($route->getModule()) . "\\Controller\\" . ucfirst($route->getPrefix()) . "\\" . ucfirst($route->getController()) . "Controller";

        if(!class_exists($class)){
            $prefixs = $route->getALLPrefix();
            if($prefixs){
                foreach ($prefixs as $v){
                    $class = "\\App\\" . ucfirst($route->getModule()) . "\\Controller\\" . ucfirst($v) . "\\" . ucfirst($route->getController()) . "Controller";
                    if(class_exists($class)){
                        $route->setPrefix($v);
                        break;
                    }
                }
            }
        }
        $method = $route->getAction();
        $action = [$class, $method];
        return $action;
    }



}