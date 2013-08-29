<?php

/*
* This file is part of the Kerisy package.
*
* (c) Lei Dong <xiaosan@outlook.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

Kerisy::import('Kerisy.Application');

class Kerisy_Application_Console extends Kerisy_Application
{
	private static $_router;
	private $_controller_object;
	
	public function run()
	{
		$this->init();
		
		global $route_request_arg;
	 	$r = $route_request_arg;
	 	if (!$r)
	 	{
	 		throw new Kerisy_Exception_500('请求错误', '10008');
	 	}
	 	
	 	$tmp = explode('/', $r);
		$module = isset($tmp[0]) ? $tmp[0] : '';
	 	$controller = isset($tmp[1]) ? $tmp[1] : '';
	 	$action = isset($tmp[2]) ? $tmp[2] : '';	 	
	 	$controller_dir = isset($_GET['d']) ? $_GET['d'] : '';
	 	
	 	//$allowModules = array('api', 'log', 'player', 'user');
	 	$allowModules = Kerisy::config()->get()->allow_modules;
	 	
	 	
	 	if (!$module || !$controller || !$action || !in_array(strtolower($module), $allowModules))
	 	{
	 		throw new Kerisy_Exception_500('模块不存在', '10007');
	 	}
	 		
	 	$module = ucfirst($module);
	 	$controller = ucfirst($controller);
	 	$controller_dir = ucfirst($controller_dir);
	 	
	 	$controller_class = $module . '_' . 'Controller_' . ( $controller_dir == '' ? '' : $controller_dir . '_')  . $controller;
	 	
	 	$action_name = lcfirst($action) . 'Action';
	 	
	 	$this->_controller_object = Kerisy::controller($controller_class);
	 	
	 	if(!in_array($action_name, get_class_methods($controller_class)))
	 	{
	 		throw new Kerisy_Exception_500('模块不存在', '10007');
	 		//throw new Kerisy_Exception_500('Unable to load action :' . $action_name . ' in the controller of ' . $controller_class);
	 	}

	 	$this->_controller_object->setAction($action);
	 	$this->_controller_object->setController($controller);
	 	$this->_controller_object->setModule($module);
	 	$this->_controller_object->setMCAID(strtolower("{$module}_{$controller}_{$action}"));
	 	
	 	$this->_controller_object->init();
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
		/*($router = Kerisy::useClass('Kerisy_Router');
	 	self::$_router = new $router();
	 	self::$_router->routing();
	 	*/
	 	//Auth
	 //	$auth = Kerisy::useClass('Kerisy_Auth');
	 //	$this->_user_info = $auth->getUserInfo();
	}
}