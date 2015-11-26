<?php
/**
 * Kerisy Framework
 * 
 * PHP Version 7
 * 
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Core
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Core;

use Kerisy\Core\Object;

/**
 * Class Route
 *
 * @package Kerisy\Core
 */
class Route extends Object
{
    protected $request;

    private $_prefix;
    private $_template;
    private $_route;
    private $_pattern;
    private $_param_keys = [];
    private $_params = [];
    private $_regular = false;
    private $_module;
    private $_controller;
    private $_action;
    private $_method;

    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }

    public function getPrefix()
    {
        return $this->_prefix;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    public function setRegular($regular)
    {
        $this->_regular = $regular;
    }

    public function getRegular()
    {
        return $this->_regular;
    }

    public function setRoute($route)
    {
        $this->_route = $route;
    }

    public function getRoute()
    {
        return $this->_route;
    }

    public function setPattern($pattern)
    {
        $this->_pattern = $pattern;
    }

    public function getPattern()
    {
        return $this->_pattern;
    }
    
    public function setParamKeys($keys)
    {
      $this->_param_keys = $keys;
    }
    
    public function getParamKeys()
    {
	return $this->_param_keys;
    }

    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function setModule($module)
    {
        $this->_module = $module;
    }

    public function getModule()
    {
        return $this->_module;
    }

    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    public function getController()
    {
        return $this->_controller;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function init()
    {
        // TODO
    }
}
