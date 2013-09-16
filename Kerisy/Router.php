<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Router
{
	//所有模块
	protected $_modules;
	protected $_default_module = 'System';
	//所有模块下的路由
	protected $_routes = array();
	
	protected $_custom_routes = array();
	
	protected $_uri;
	protected $_uri_string;
	
	protected $_module = 'System';
	protected $_controller = 'Index';
	protected $_action = 'Index';
	
	protected $_controller_class;
	
	public function __construct()
	{
		$this->_uri = Kerisy::loadClass('Kerisy_Uri');
		$this->initModules();
		$this->initRoutes();
	}

	public function routing()
	{
		$this->_uri_string = $this->_uri->getUriString();

		if(isset($this->_routes[$this->_uri_string]))
		{
			$this->setRequest($this->_routes[$this->_uri_string]['route']);
			return ;
		}

		if (is_array($this->_routes))
		{
			foreach ($this->_routes as $key => $route)
			{
				if (!$route['regular'])
				{
					continue;
				}
				
				if (preg_match($route['pattern'], $this->_uri_string, $maches))
				{
					foreach ($route['params'] as $i => $key)
					{
						$_GET[$key] = $maches[$i+1];
					}
	
					$this->setRequest($route['route']);
					return ;
				}
			}
		}

		$this->setRequest($this->_uri_string);
	}
	
	protected function initModules()
	{
		if (Kerisy::config()->get()->default_module)
		{
			$this->_default_module = Kerisy::config()->get()->default_module;
		}
		
		$this->_modules = Kerisy::config()->get()->modules;
	}
	
	protected function initRoutes()
	{
		$this->_custom_routes = $this->_routes = array();
		
		$routes = Kerisy::config()->get()->routes;
		if(is_array($routes))
		{
			foreach ($routes as $pattern => $route)
			{
				$this->addRoute($pattern, $route);
			}
		}

		foreach ($this->_modules as $module)
		{
			if ($routes = Kerisy::config($module)->get('routes')->routes)
			{
				foreach ($routes as $pattern => $route)
				{
					$this->addRoute($pattern, $route);
				}
			}
		}
	}
	
	protected function addRoute($pattern, $route)
	{
		$template = '/' . trim($pattern, '/') . '/';
		
		$route_data = array(
				'pattern' => $pattern,
				'template' => $template,
				'regular' => false,
				'route' => $route,
				'params' => array()
		);
		
		if(preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches))
		{
			// $params = array_combine($matches[1], $matches[2]);

			for ($i = 0; $i < count($matches[0]); $i++)
			{
				$params[$i] = $matches[1][$i];
				$reg = $matches[2][$i] ? $matches[2][$i] : '.*';
				$pattern = str_replace($matches[0][$i], "({$reg})", $pattern);
				$template = str_replace($matches[0][$i], "{{$matches[1][$i]}}", $template);
			}
			
			$pattern = str_replace('/', '\/', $pattern);
			
			$route_data['regular'] = true;
			$route_data['pattern'] = "/^{$pattern}$/";
			$route_data['template'] = $template;
			$route_data['params'] = $params;
		}

		$this->_custom_routes[$route] = $route_data;
		$this->_routes[$route_data['pattern']] = $route_data;
	}
	
	public function createUrl($route, $params = array())
	{
		if (array_key_exists($route, $this->_custom_routes))
		{
			$router = $this->_custom_routes[$route];
			$url = $router['template'];
			foreach ($router['params'] as $param)
			{
				$url = str_replace("{{$param}}", isset($params[$param]) ? $params[$param] : '', $url);
				unset($params[$param]);
			}
		}
		else
		{
			$url = '/' . trim($route, '/') . '/';
		}

		if (count($params)>0)
		{
			$url .= '?' . http_build_query($params);
		}
		
		return $url;
	}
	
	protected function setRequest($uri_string)
	{
		if(empty($uri_string))
		{
			$uri_string = $this->_uri_string;
		}
		
		$module = $controller = $action = '';
		
		if ($uri_string != '/')
		{
			$segments = explode('/', $uri_string);
	
			$module		= isset($segments[0]) ? $segments[0] : '';
			$controller	= isset($segments[1]) ? $segments[1] : '';
			$action		= isset($segments[2]) ? $segments[2] : '';
		}

		$this->setModule($module);
		$this->setController($controller);
		$this->setAction($action);
	}
	
	protected function setModule($module)
	{
		if($module)
		{
			$this->_module = ucfirst($module);
		}
		else
		{
			$this->_module = ucfirst($this->_default_module);
		}
	}
	
	public function getModule()
	{
		//TODO 判断模块是否开启
		return $this->_module;
	}
	
	protected function setController($controller)
	{
		if($controller)
		{
			$this->_controller = ucfirst($controller);
		}
		else
		{
			$this->_controller = ucfirst($this->getModuleDefaultController($this->_uri->getAdmincp() ? 'admincp' : 'front'));
		}
	}
	
	public function getController()
	{
		return $this->_controller;
	}
	
	protected function setAction($action)
	{
		if($action)
		{
			$this->_action = ucfirst($action);
		}
		else
		{
			$this->_action = ucfirst($this->getDefaultAction($this->_uri->getAdmincp() ? 'admincp' : 'front'));
		}
	}
	
	public function getAction()
	{
		return $this->_action;
	}
	
	protected function getModuleDefaultController($type = 'front')
	{
		$this->_module = ucfirst($this->_module);
		$module_class_name = $this->_module . '_' . $this->_module;
		
		try 
		{
			Kerisy::module($module_class_name);
			$tmp_array = $module_class_name::info();
			return $tmp_array[$type . '_default_controller'];
		} 
		catch (Exception $e) 
		{
			throw new Kerisy_Exception_500('Unable to find class: ' . $module_class_name);
		}
	}
	
	protected function getDefaultAction($type = 'front')
	{
		$controller_class = $this->_module . '_Controller_' . ($type === 'admincp' ? 'Admin_' : '') . $this->_controller;		

		$default_action = Kerisy::controller($controller_class)->getDefaultAction();
		
		return $default_action;
	}
	
	public function getControllerDirectory()
	{
		return $this->_uri->getAdmincp() ? 'Admin' : '';
	}
}