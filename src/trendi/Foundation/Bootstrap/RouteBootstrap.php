<?php
/** 
 * 路由初始化
 * 
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午3:38
 */

namespace Trendi\Foundation\Bootstrap;

use Route;
use Trendi\Config\Config;
use Trendi\Support\Dir;

class RouteBootstrap
{
    protected static $instance = null;

    /**
     *  instance
     * @return \Trendi\Foundation\Bootstrap\RouteBootstrap
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
        $this->load();
        $this->loadFromConfig();
    }

    /**
     * 路由导入
     *
     * @throws \Trendi\Support\Exception\InvalidArgumentException
     */
    public function load()
    {
        $path = Config::get("route.load_path");

        if ($path) {
            $dir = Dir::formatPath($path);
            if (is_dir($dir)) {
                $configFiles = Dir::glob($dir, '*.php', Dir::SCAN_BFS);

                foreach ($configFiles as $file) {
                    require_once $file;
                }
            }
        }
    }

    /**
     * 通过配置加载route
     */
    public function loadFromConfig()
    {
        $config = Config::get("route.routes");
        if ($config) {
            foreach ($config as $value){
                $this->loadOneRouteConfig($value);
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
        if($isGroup){
            Route::group($config,function() use($config){
                $this->loadSingle($config);
            });
        }else{
            $this->loadSingle($config);
        }
    }

    /**
     * single 处理
     * @param $config
     */
    private function loadSingle($config)
    {
        $routes = isset($config['routes'])?$config['routes']:[];
        if($routes){
            foreach ($routes as $v){
                $method = isset($v['method'])?$v['method']:[];
                $_method = $method=="*"?"any":$method;
                $_method = $_method?$_method:"any";
                Route::bind($_method, [$v['path'],$v]);
            }
        }
    }

}