<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

Kerisy::import('Kerisy.Application');

class Kerisy_Application_Web extends Kerisy_Application
{
	private static $_router;
	private $_controller_object;
	private $_user_info;
	
	public function run()
	{
	 	$module = self::router()->getModule();
	 	$controller = self::router()->getController();
	 	$action = self::router()->getAction();
	 	$controller_dir = self::router()->getControllerDirectory();

	 	if (!$module || !$controller || !$action)
	 	{
	 		throw new Kerisy_Exception_500('Error');
	 	}
	 	
	 	$controller_class = $module . '_' . 'Controller_' . ( $controller_dir == '' ? '' : $controller_dir . '_')  . $controller;

	 	$action_name = lcfirst($action) . 'Action';
	 	
	 	$this->_controller_object = Kerisy::controller($controller_class);
	 	
	 	if(!in_array($action_name, get_class_methods($controller_class)))
	 	{
	 		throw new Kerisy_Exception_500('Unable to load action :' . $action_name . ' in the controller of ' . $controller_class);
	 	}
	 	
	 	$this->_controller_object->setAction($action);
	 	$this->_controller_object->setController($controller);
	 	$this->_controller_object->setModule($module);
	 	$this->_controller_object->setMCAID(strtolower("{$module}_{$controller}_{$action}"));
	 	$this->_controller_object->before();
	 	$this->_controller_object->$action_name();
	 	$this->_controller_object->after();
	}

	public static function router()
	{
		return self::$_router;
	}
	
	public function init()
	{
		//TODO 从数据库读取 option 表配置，所有默认的模块、模板之类的都是从数据库读取配置信息
		$router = Kerisy::useClass('Kerisy_Router');
	 	self::$_router = new $router();
	 	self::$_router->routing();
	}
}