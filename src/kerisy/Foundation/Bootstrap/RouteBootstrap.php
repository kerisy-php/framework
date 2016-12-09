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
    public static function getInstance()
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self();
    }

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->loadFromConfig();
    }

    /**
     * 通过配置加载route
     */
    public function loadFromConfig()
    {
        $config = Config::get("route");
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
                    $tmp = [];
                    $tmp['method'] = $method;
                    $where = [];
                    if (stristr($path, "<")) {
                        preg_match_all("/\<(.*?)\:([^\>]+)\>/", $path, $matches, PREG_SET_ORDER);
                        if ($matches) {
                            foreach ($matches as $match) {
                                $key = array_isset($match, 1);
                                $value = array_isset($match, 2);
                                if ($key && $value) {
                                    $where[$key] = $value;
                                }
                            }
                        }
                        $path = preg_replace("/\<(.*?)\:([^\>]+)\>/", '{$1}', $path);
                    }

                    list($modules, $controller, $action) = explode("/", $uses);

                    $realUses = "\\App\\" . ucwords($modules) . "\\Controller\\" . ucwords($groupName) . "\\".ucwords($controller)."Controller@" . $action;

                    $tmp['path'] = $path;
                    $tmp['uses'] = $realUses;
                    $tmp['name'] = $uses;
                    $tmp['middleware'] = $middleware;
                    $tmp['where'] = $where;
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
        $isGroup = isset($config['name']) && $config['name'] &&
            isset($config['prefix']) && $config['prefix'];
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
                if (!isset($v['path']) && !isset($v['method'])) {
                    list($path, $method, $uses, $name, $where, $domain, $middleware) = $v;
                    $v['method'] = $method;
                    $v['path'] = $path;
                    $v['uses'] = $uses;
                    $v['name'] = $name;
                    $v['where'] = $where;
                    $v['domain'] = $domain;
                    $v['middleware'] = $middleware;
                }
                $method = isset($v['method']) ? $v['method'] : [];
                $_method = $method == "*" ? "any" : $method;
                $_method = $_method ? $_method : "any";
                Route::bind($_method, [$v['path'], $v]);
            }
        }
    }

}