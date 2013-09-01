<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

Kerisy::import('Kerisy.Exception.401');
Kerisy::import('Kerisy.View');

class Kerisy_Controller
{
	public  $view;
	protected  $base_url;
	
	protected $action;
	protected $controller;
	protected $module;
	protected $mca_id;
	
	protected $logged_user_info;
	
	protected $crumb = array();
	
	public function __construct()
	{
		session_start();
		$this->initView();
		$this->initBaseUrl();
	}

	public function createUrl($route, $params = array())
	{
		if (!$this->_router) {
			throw new Kerisy_Exception_500('not set router.');
		}
		
		return $this->base_url . Kerisy::router()->createUrl($route, $params);
	}
	
	private function initView()
	{
		$config = array(
			'template_dir' => APP_PATH . 'templates' . DS . 'front' . DS . 'default' . DS,
			'template_engine' => 'Kerisy'
		);
		
		$this->view = new Kerisy_View();
		$this->view->init($config);
		
		return $this->view;
	}
	
	public function getDefaultAction()
	{
		return 'index';
	}
	
	public function before()
	{
		$this->auth();
		//TODO
		//echo '<br />action executed before<br />';
	}
	
	public function after()
	{
		//TODO
		//echo '<br />action executed after<br />';
	}
	
	public function auth()
	{
		//TODO
		//throw new Kerisy_Exception_401('Deny access!');
	}
	
	public function getAuthedAction()
	{
		return array();
	}

	
	public function redirect($uri)
	{
		if (strstr($uri, '://'))
		{
			header('Location: ' . $uri);
		}
		else
		{
			header('Location: ' . $this->baseUrl() . preg_replace('/^\//', '', $uri) );
		}
		exit;
	}

	public function baseUrl()
	{
		return $this->base_url;
	}
	
	protected function initBaseUrl()
	{
		$this->base_url = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
		
		if ($this->base_url == '/')
		{
			$this->base_url = '';
		}
		
		$this->view->base_url = $this->base_url;
	}
	
	protected function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}
	
	protected function isGet()
	{
		return $_SERVER['REQUEST_METHOD'] === 'GET';
	}
	
	public function setAction($action)
	{
		$this->action = $action;
	}
	
	public function setController($controller)
	{
		$this->controller = $controller;
	}
	
	public function setModule($module)
	{
		$this->module = $module;
	}
	
	public function getAction()
	{
		return $this->action;
	}
	
	public function getController()
	{
		return $this->controller;
	}
	
	public function getModule()
	{
		return $this->module;
	}
	
	
	public function setLoggedUserInfo($user_info = null)
	{
		$this->logged_user_info = $user_info;
	}
	
	
	public function setMCAID($mca_id)
	{
		$this->view->mac_id = $this->mca_id = $mca_id;
	}
	
	public function crumb($crumb_data = array())
	{
		if (empty($crumb_data) || !isset($crumb_data['name']))
		{
			return false;
		}
		
		return $this->_crumb($crumb_data['name'], $crumb_data['url'] ? $crumb_data['url'] : null, isset($crumb_data['class']) ? $crumb_data['class'] : null);
	}
	
	private function _crumb($name, $url = '', $class = '')
	{
		if (!$name)
		{
			return  false;
		}
		
		$this->crumb[] = array(
			'name' => $name,
			'url' => $url,
			'class' => $class
		);
		
		$this->view->crumb = $this->crumb;
	}
	//不需要登录验证的
	public function getAllowedRules()
	{
		return array();
	}
	//需要登录验证的
	public function getAuthRules()
	{
		return array();
	}
}