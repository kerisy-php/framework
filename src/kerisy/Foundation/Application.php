<?php
/**
 * 项目初始入口
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

namespace Kerisy\Foundation;

use Kerisy\Console\Application as CmdApplication;
use Kerisy\Config\Config as CConfig;
use Kerisy\Foundation\Bootstrap\Bootstrap;
use Kerisy\Foundation\Bootstrap\RouteBootstrap;
use Kerisy\Foundation\Command;
use Kerisy\Mvc\Route\RouteMatch;
use Kerisy\Support\Dir;
use Kerisy\Support\Arr;
use Kerisy\Support\Log;


class Application
{

    /**
     * 项目路径
     *
     * @var string
     */
    protected static $rootPath = null;

    public function __construct($rootPath)
    {
        self::$rootPath = Dir::formatPath($rootPath);
    }
    
    public function httpBoostrap()
    {
        $this->initRelease();
        RouteBootstrap::getInstance();
    }

    protected function initRelease()
    {
        $release = CConfig::get("app.view.fis.compile_path");
        if(is_dir($release)){
           CConfig::set("_release.path", $release);
        }
    }

    /**
     * rpc server 初始化
     */
    public function rpcBootstrap()
    {
        RouteBootstrap::getInstance();
    }


    /**
     * https server 路由开始匹配
     *
     * @param $request
     * @param $response
     * @return mixed
     */
    public function start($request, $response)
    {
       
        $url = $request->getPathInfo();
        
        $routeObj = RouteMatch::getInstance();
       
        $middlewareConfig = CConfig::get("app.middleware");
        
        $routeObj->setMiddlewareConfig($middlewareConfig);
       
        $resut = $routeObj->run($url, $request, $response);
        
        return $resut;
    }

    /**
     * 获取项目根目录
     *
     * @return string
     */
    public static function getRootPath()
    {
        return self::$rootPath;
    }

    /**
     * Command 初始化
     *
     * @throws \Exception
     */
    public static function runCmd()
    {
        $commands = [
            new Command\Httpd\Start(),
            new Command\Httpd\Restart(),
            new Command\Httpd\Status(),
            new Command\Httpd\Stop(),
            new Command\Rpc\Start(),
            new Command\Rpc\Restart(),
            new Command\Rpc\Status(),
            new Command\Rpc\Stop(),
            new Command\Job\Start(),
            new Command\Job\Restart(),
            new Command\Job\Status(),
            new Command\Job\Stop(),
            new Command\Job\Clear(),
            new Command\Server\Start(),
            new Command\Server\Restart(),
            new Command\Server\Status(),
            new Command\Server\Stop(),
            new Command\Artisan\Optimize(),
            new Command\Artisan\Clean(),
        ];
        $config = CConfig::get("app.command");
        if ($config) {
            $commands = Arr::merge($commands, $config);
        }
        $application = new CmdApplication();
        foreach ($commands as $v) {
            $application->add($v);
        }
       
        $application->run();
    }

}