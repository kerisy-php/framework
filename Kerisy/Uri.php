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
	private $_admincp = false;
	private $_uri_string;
	private $_uri_mode;
	
	public function __construct()
	{
		$this->_uri_mode = Kerisy::config()->get()->uri_mode;
		$this->_uri_string = $this->detectUri();
		$this->setAdmincp();
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
	
	public function getAdmincp()
	{
		return $this->_admincp;
	}
	
	private function setAdmincp()
	{
		if ('domain' == $this->_uri_mode['admincp']['type'])
		{
			if ($_SERVER['HTTP_HOST'] == $this->_uri_mode['admincp']['value'])
			{
				$this->_admincp = true;
			}
		}
		else 
		{
			$tmp = explode('/', $this->_uri_string);
			
			if ($this->_uri_mode['admincp']['value'] == $tmp[0])
			{
				unset($tmp[0]);
				$this->_uri_string = implode('/', $tmp); 
				$this->_uri_string = empty($this->_uri_string) ? '/' : $this->_uri_string;
				$this->_admincp = true;
			}
		}
	}
}
