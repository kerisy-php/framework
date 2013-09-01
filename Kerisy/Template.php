<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Template
{
	/*
	 * 变量数组
	 * */
	private $_vars			= array();
	
	/*
	 * 模板扩展名
	 * */
	private $_ext			= '.tpl.php';
	
	/*
	 * 默认模板目录
	 * */
	private $_template_dir	= 'templates/default/';

	/*
	 * 加载的脚本文件
	 * */
	private $_script_data	= array();

	/*
	 * 加载的样式文件
	 * */
	private $_css_data		= array();

	public function __construct()
	{
		$this->_router = Kerisy::loadClass('Kerisy_Router');
	}
	
	public function __get($key)
	{
		return isset($this->_vars[$key]) ? $this->_vars[$key] : null;
	}
	
	public function __set($key, $value)
	{
		$this->_vars[$key] = $value;
		return $this;
	}
	
	public function display($template_file)
	{
		$file_name = $this->_template_dir . $template_file . $this->_ext;

		include $file_name;
	}

	public function assign($var, $value)
	{
		if (is_array($var))
		{
			foreach ($var as $k => $v)
			{
				$this->assign($k, $v);
			}
		}
		else
		{
			$this->_vars[$var] = $value;
		}
		
		return $this;
	}

	public function render($template_file)
	{
		$file_name = $this->_template_dir . $template_file . $this->_ext;

		ob_start();

		include $file_name;

		$_output = ob_get_clean();
		
		return $_output;
	}
	
	public function setTemplateDir($dir)
	{
		$dir = rtrim(rtrim($dir, '/'), '\\') . DS;
		$this->_template_dir = $dir;
    	return $this;
	}
	
	public function getTemplateDir()
	{
		return $this->_template_dir;
	}
	
	public function addScript($script_url)
	{
		$this->_script_data[md5($script_url)] = $script_url;
		return $this;
	}
	
	public function getScriptData()
	{
		return $this->_script_data;
	}
	
	public function addCss($css_url)
	{
		$this->_css_data[md5($css_url)] = $css_url;
		return $this;
	}
	
	public function getCssData()
	{
		return $this->_css_data;
	}
	
	public function createUrl($route, $params = array())
	{
		return $this->base_url . Kerisy::router()->createUrl($route, $params);
	}
}
