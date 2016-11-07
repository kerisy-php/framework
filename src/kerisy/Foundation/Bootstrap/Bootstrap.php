<?php
/**
 *  初始化
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Bootstrap;

use Kerisy\Config\Config;
use Kerisy\Di\Di;
use Kerisy\Foundation\Bootstrap\Config\AliasConfig;
use Kerisy\Foundation\Bootstrap\Config\DiConfig;
use Kerisy\Foundation\Bootstrap\Config\TaskConfig;
use Kerisy\Server\Task;
use Kerisy\Server\Pool;
use Kerisy\Support\AliasLoader;
use Kerisy\Support\Arr;
use Kerisy\Support\Facade;
use Kerisy\Support\RunMode;
use Kerisy\Support\Dir;
use Kerisy\Coroutine\Event;
use Kerisy\Http\Response;
use Kerisy\Foundation\Controller as HttpController;
use Kerisy\Rpc\Controller as RpcController;
use Kerisy\Support\Log;

class Bootstrap
{
    protected static $instance = [];

    /**
     *  instance
     * @return object
     */
    public static function getInstance($path)
    {
        if (isset(self::$instance[$path]) && self::$instance[$path]) return self::$instance[$path];

        return self::$instance[$path] = new self($path);
    }

    /**
     * Init constructor.
     */
    public function __construct($path)
    {
        $this->initEnv();
        $this->initConfig($path);
        $this->iniSet();
        $this->initMonitor();
        $this->initAlias();
        $this->initHelper();
        $this->initDi();
        $this->initFacade();
        $this->initTask();
        $this->init404();
        $this->initEvent();
    }

    public function initEvent()
    {
        /**
         * 请求结束清空连接池与worker进程对应关系
         */
        Event::bind("request.end", function($workerId){
            unset(Pool::$poolTaskData[$workerId]);
        });
    }

    /**
     * 404  处理
     */
    protected function init404()
    {
        Event::bind("404",function($allParams){
            list($e, $errorName, $params) = $allParams;
            $config = Config::get("app.view.page404");

            if($errorName == "Page404Exception"){
                Log::debug($e->getMessage());
            }

            if($params instanceof Response){
                $controller = new HttpController();
                if($config){
                    $content = $controller->render($config);
                }else{
                    $content = "Page Not Found";
                }
                $params->end($content);
            }else{
                list($server, $fd, $adapter) = $params;
                $controller = new RpcController();
                $content = $controller->response("", RpcController::RESPONSE_NORMAL_ERROR_CODE, "API Not Found");
                $content = $adapter->getSerialize()->format($content);
                $server->send($content);
                $server->close($fd);
            }
        });
    }

    /**
     * 监控初始化
     */
    protected function initMonitor()
    {

    }

    /**
     * php.ini 初始化
     */
    protected function iniSet()
    {
        $configApp = Config::get("app");
        if (isset($configApp['memory_limit'])) {
            ini_set('memory_limit', $configApp['memory_limit']);
        }
        if (isset($configApp['date_default_timezone_set'])) {
            date_default_timezone_set($configApp['date_default_timezone_set']);
        } else {
            date_default_timezone_set('Asia/Shanghai');
        }

    }

    /**
     * task 初始化
     * @return bool
     */
    protected function initTask()
    {
        $options = TaskConfig::getOptions();

        $configOption = Config::get("app.task");
        if ($configOption) $options = Arr::merge($options, $configOption);

        if (!$options) return true;
        Task::setTaskConfig($options);

        return true;
    }

    /**
     * 帮助函数初始化
     */
    protected function initHelper()
    {
        if(function_exists('url') && function_exists('redis') && function_exists('config')){

        }else{
            require_once "helper.php";
        }
    }

    /**
     * 配置初始化
     * 
     * @param $path
     */
    protected function initConfig($path)
    {
        $path = Dir::formatPath($path);
        Config::setConfigPath($path . "config");
    }

    /**
     * 'init runenv
     */
    protected function initEnv()
    {
        RunMode::init();
        ErrorHandleBootstrap::getInstance();
    }


    /**
     * init alias
     * @return bool
     */
    protected function initAlias()
    {
        $options = AliasConfig::getOptions();

        $configOption = Config::get("app.aliases");
        if ($configOption) $options = Arr::merge($options, $configOption);

        if (!$options) return true;
        AliasLoader::getInstance($options)->register();

        return true;
    }

    /**
     *  facade init
     */
    protected function initFacade()
    {
        Facade::clearFacadeInstances();
        Facade::setFacadeApplication(Di::getContainer());
    }

    /**
     *  Di init
     *
     * @return bool
     */
    protected function initDi()
    {
        $options = DiConfig::getOptions();

        $configOption = Config::get("app.di");
        if ($configOption) $options = Arr::merge($options, $configOption);

        if (!$options) return true;

        foreach ($options as $k => $v) {
            Di::set($k, $v);
        }
        return true;
    }


}