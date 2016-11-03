<?php
/**
 *  route 基础类
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

use Kerisy\Mvc\Route\Base\Route as BaseRoute;
use Kerisy\Support\Arr;

class RouteBase
{
    const DEFAULTGROUP_KEY = "group_default";

    protected $path = null;
    protected $defaults = [];
    protected $requirements = [];
    protected $host = "";
    protected $methods = [];
    protected $name = null;
    protected static $result = [];
    protected $resultKey = null;
    /**
     * @var \Kerisy\Mvc\Route\Base\Route
     */
    protected $routeObj = null;

    /**
     * 设置route 条件
     *
     * @param $key
     * @param string $value
     * @return $this
     */
    public function where($key, $value = "")
    {
        if (is_array($key)) {
            $this->requirements = Arr::merge($this->requirements, $key);
        }

        if (is_string($key)) {
            $tmp = [$key => $value];
            $this->requirements = Arr::merge($this->requirements, $tmp);
        }
        
        $this->routeObj->setRequirements($this->requirements);

        $this->setResult($this->resultKey, $this->routeObj);

        return $this;
    }

    /**
     * 设置toute 名称
     *
     * @param $key
     * @return $this
     */
    public function name($key)
    {
        $this->name = $key;

        $preKey = self::DEFAULTGROUP_KEY;

        if (RouteGroup::getGroupHash()) {
            $preKey = RouteGroup::getGroupHash();
        }

        unset(self::$result[$preKey][$this->resultKey]);

        $this->setResult($this->name, $this->routeObj);
        $this->resultKey = $this->name;

        return $this;
    }

    /**
     * 获取名称
     *
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * defaults
     *
     * @param array $default
     * @return $this
     */
    public function defaults(array $default)
    {
        $this->defaults = Arr::merge($this->defaults, $default);

        $this->routeObj->setDefaults($this->defaults);

        $this->setResult($this->resultKey, $this->routeObj);

        return $this;
    }

    /**
     * 中间件
     *
     * @return $this|void
     */
    public function middleware()
    {

        $numargs = func_num_args();
        $argList = func_get_args();

        if ($numargs == 0) return;

        $tmp = [];
        for ($i = 0; $i < $numargs; $i++) {
            if (is_array($argList[$i])) {
                $tmp = Arr::merge($tmp, $argList[$i]);
            } else {
                $tmp[] = $argList[$i];
            }
        }

        $default = ['_middleware' => $tmp];

        $this->defaults($default);

        return $this;
    }

    /**
     * 域名设置
     *
     * @param $host
     * @return $this
     */
    public function domain($host)
    {
        $this->host = $host;

        $this->routeObj->setHost($this->host);

        $this->setResult($this->resultKey, $this->routeObj);

        return $this;
    }

    /**
     * 匹配
     *
     * @param $methods
     * @param $path
     * @param $closureOrArr
     * @return $this
     */
    public function match($methods, $path, $closureOrArr)
    {
        if($methods != 'any'){
            if (!is_array($methods)) $methods = array($methods);
            $this->methods = $methods;
        }
        
        $this->path = $path;
        
        if (!is_array($closureOrArr)) {
            $this->defaults['_controller'] = $closureOrArr;
        }

        $route = new BaseRoute(
            $this->path, // path
            $this->defaults, // default values
            $this->requirements, // requirements
            [], // options
            $this->host, // host
            [], // schemes
            $this->methods // methods
        );

        $this->routeObj = $route;
        $this->resultKey = spl_object_hash($route);

        $this->setResult($this->resultKey, $route);

        return $this;
    }

    /**
     * 设置
     * @param $key
     * @param $value
     */
    protected function setResult($key, $value)
    {
        $preKey = self::DEFAULTGROUP_KEY;

        if (RouteGroup::getGroupHash()) {
            $preKey = RouteGroup::getGroupHash();
        }
        self::$result[$preKey][$key] = $value;
    }


    /**
     * @param string $key
     * @return array|mixed
     */
    public static function getResult($key = '')
    {
        if (!$key) {
            return self::$result;
        }
        return isset(self::$result[$key]) ? self::$result[$key] : [];
    }

    /**
     * 清空
     * @param string $key
     */
    public static function clearResult($key = self::DEFAULTGROUP_KEY)
    {
        unset(self::$result[$key]);
    }

    public function __destruct()
    {
    }

}