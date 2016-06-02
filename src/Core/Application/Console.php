<?php
/**
 * Created by PhpStorm.
 * User: IceQi
 * Date: 2016/3/15
 * Time: 14:59
 */

namespace Kerisy\Core\Application;


use Kerisy\Core\Application;
use Kerisy\Core\Dispatcher;
use Kerisy\Core\Route;

class Console extends Application
{


    public function __construct()
    {

        parent::__construct();
        $this->init();
        $this->bootstrap();
    }



    public function bootstrap()
    {
        if (!$this->bootstrapped) {
            $this->initializeConfig();
            $this->registerComponents();
            //$this->registerEntities();
            $this->bootstrapped = true;

        }

        return $this;
    }
    protected function runAction($action, $request=false, $response=false)
    {
        $data = call_user_func_array($action, [$request, $response]);

        return $data;
    }


    public function makeExecuor($router){
        $this->execExecuor($router);
    }

    protected function execExecuor($router)
    {

        $this->dispatcher = new Dispatcher();

        $route = $this->dispatcher->getRouter()->getRouteByPath($router);

        $command = $this->createCommand($route);

       return $this->runCommand($command);

    }


    protected function createCommand(Route $route)
    {
        $class = "App\\" . ucfirst($route->getModule()) . "\\Execuor\\"  . ucfirst($route->getController()) . "Execuor";
        $method = $route->getAction();
        //$controller = $this->get($class);
        $controller = \Kerisy::make($class);
        //$controller->callMiddleware();

        $action = [$controller, $method];

        return $action;
    }

    protected function runCommand($action, $request=false, $response=false)
    {
        $data = call_user_func_array($action, [$request, $response]);

        return $data;
    }

}