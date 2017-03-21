<?php
/**
 * 路由初始化
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

namespace Kerisy\Foundation\Bootstrap;

use Kerisy\Config\Config;
use Route;

class RouteBootstrap
{
    protected static $instance = null;

    /**
     *  instance
     * @return \Kerisy\Foundation\Bootstrap\RouteBootstrap
     */
    public static function getInstance($configKey='route')
    {
        if (self::$instance[$configKey]) return self::$instance[$configKey];

        return self::$instance[$configKey] = new self($configKey);
    }

    /**
     * constructor.
     */
    public function __construct($configKey='route')
    {
        $this->loadFromConfig($configKey);
    }

    /**
     * 通过配置加载route
     */
    public function loadFromConfig($configKey)
    {
        $config = Config::get($configKey);
        $this->change($config);
//        dump($config);
        if ($config) {
            foreach ($config as $value) {
                $this->loadOneRouteConfig($value);
            }
        }
    }

    /**
     * 配置转换
     * @param $configAll
     */
    protected function change(&$configAll)
    {
        foreach ($configAll as &$config) {
            $groupName = $config['name'];
            $routes = array_isset($config, 'routes');
            if ($routes) {
                $newRoutes = [];
                foreach ($routes as $v) {
                    $method = array_isset($v, 0);
                    $path = array_isset($v, 1);
                    $uses = array_isset($v, 2);
                    $middleware = array_isset($v, 3);
                    $defaults = array_isset($v, 4);
                    $tmp = [];
                    $tmp['method'] = $method;

                    $where = [];
                    if (stristr($path, "<")) {
                        preg_match_all("/\<(.*?)\:([^\>]+)\>/", $path, $matches, PREG_SET_ORDER);
                        if ($matches) {
                            foreach ($matches as $match) {
                                $key = array_isset($match, 1);
                                if(stristr($key, "=")){
                                    list($key,$_v) = explode("=", $key);
                                    $defaults[$key] = $_v;
                                }
                                $value = array_isset($match, 2);
                                if ($key && $value) {
                                    $where[$key] = $value;
                                }
                            }
                        }
                        $path = preg_replace_callback("/\<(.*?)\:([^\>]+)\>/", function($match){
                            $key = array_isset($match, 1);
                            if(stristr($key, "=")){
                                list($key,$_v) = explode("=", $key);
                            }
                            return "{".$key."}";
                        },$path);
                    }

                    list($modules, $controller, $action) = explode("/", $uses);

                    $realUses = "\\App\\" . ucwords($modules) . "\\Controller\\" . ucwords($groupName) . "\\".ucwords($controller)."Controller@" . $action;

                    $tmp['path'] = $path;
                    $tmp['uses'] = $realUses;
                    $tmp['name'] = $uses;
                    $tmp['middleware'] = $middleware;
                    $tmp['where'] = $where;
                    $tmp['defaults'] = $defaults;
                    $newRoutes[] = $tmp;
                }
                $config['routes'] = $newRoutes;
            }
        }
    }

    /**
     * group single 处理
     *
     * @param $config
     */
    private function loadOneRouteConfig($config)
    {
        $isGroup = false;
        if((isset($config['name']) && $config['name']) ||
            (isset($config['prefix']) && $config['prefix']) ||
            (isset($config['domain']) && $config['domain']) ||
            (isset($config['middleware']) && $config['middleware']) ||
            (isset($config['methods']) && $config['methods'])
        ){
            $isGroup = true;
            if(!isset($config['name']) || !$config['name']){
//                $config['name'] = md5(serialize($config));
                throw new \Exception("group route name is null!");
            }
        }

        if ($isGroup) {
            Route::group($config, function () use ($config) {
                $this->loadSingle($config);
            });
        } else {
            $this->loadSingle($config);
        }
    }

    /**
     * single 处理
     * @param $config
     */
    private function loadSingle($config)
    {
        $routes = isset($config['routes']) ? $config['routes'] : [];
        if ($routes) {
            foreach ($routes as $v) {
                $method = isset($v['method']) ? $v['method'] : [];
                $_method = $method == "*" ? "any" : $method;
                $_method = $_method ? $_method : "any";
                $v['method'] = $_method;
                Route::bind($_method, [$v['path'], $v]);
            }
        }
    }

}