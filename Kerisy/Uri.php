<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Uri
{
	private $_uri_string;
	private $_uri_mode;
	private $_controller_prefix_domains = array();
	private $_controller_prefix_routes = array();
	private $_use_controller_prefix = false;
	private $_controller_prefix = '';


	public function __construct()
	{
		$this->_uri_mode = Kerisy::config()->get()->uri_mode;
		
		$data = Kerisy::config()->get()->controller_custom_prefix;
		if ($data)
		{
			$this->setControllerCustomPrefix($data);
		}
		
		$this->_uri_string = $this->detectUri();
		$this->filterControllerPrefix();
	}
	
	public function setControllerCustomPrefix($prefix_data = array())
	{
		foreach ($prefix_data as $k => $v)
		{
			if ($v['type'] == "domain")
			{
				$this->_controller_prefix_domains[$v['value']] = $k;
			}
			else
			{
				$this->_controller_prefix_routes[$v['value']] = $k;
			}
		}
	}
	
	public function getUriString()
	{
		return $this->_uri_string;
	}
	
	private function detectUri()
	{
		if (!empty($_SERVER['PATH_INFO']))
		{
			$uri = $_SERVER['PATH_INFO'];
		}
		else
		{
			if (isset($_SERVER['REQUEST_URI']))
			{
				$uri = $_SERVER['REQUEST_URI'];

				if ($request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
				{
					$uri = $request_uri;
				}
				
				$uri = rawurldecode($uri);
			}
			else if (isset($_SERVER['PHP_SELF']))
			{
				$uri = $_SERVER['PHP_SELF'];
			}
			else if (isset($_SERVER['REDIRECT_URL']))
			{
				$uri = $_SERVER['REDIRECT_URL'];
			}
			else
			{
				throw new Kerisy_Exception_500('Unable to get uri!');
			}
		}
		
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		else if (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}
		
		if (strncmp($uri, '?/', 2) === 0)
		{
			$uri = substr($uri, 2);
		}
		
		$parts = preg_split('/\?/i', $uri, 2);
		
		$uri = $parts[0];
	
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		
		if ($uri == '/' || empty($uri))
		{
			return '/';
		}

		$uri = parse_url($uri, PHP_URL_PATH);

		return str_replace(array('//', '../'), '/', trim($uri, '/'));
	}
	
	public function useControllerPrefix()
	{
		return $this->_use_controller_prefix;
	}
	
	public function getControllerPrefix()
	{
		return $this->_controller_prefix;
	}
	
	private function filterControllerPrefix()
	{
		if (array_key_exists($_SERVER['HTTP_HOST'], $this->_controller_prefix_domains))
		{
			$this->_use_controller_prefix = true;
			$this->_uri_mode = "domain";
			$this->_controller_prefix = $this->_controller_prefix_domains[$_SERVER['HTTP_HOST']];
		}
		else
		{
			$tmp = explode('/', $this->_uri_string);

			if (count($tmp) && array_key_exists($tmp[0], $this->_controller_prefix_routes))
			{
				$route_dir = $tmp[0];
				if (count($tmp) > 1)
				{
					unset($tmp[0]);

					$this->_uri_string = implode('/', $tmp); 
					$this->_uri_string = empty($this->_uri_string) ? '/' : $this->_uri_string;
				}
				else
				{
					$this->_uri_string = '';
				}
				
				$this->_use_controller_prefix = true;
				$this->_uri_mode = "route";
				$this->_controller_prefix = $this->_controller_prefix_routes[$route_dir];
			}
		}
	}
}
