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
	
	private function __get($key)
	{
		return $this->_vars[$key];
	}
	
	private function __set($key, $value)
	{
		$this->_vars[$key] = $value;
		return $this;
	}
	
	public function display($template_file)
	{
		echo $this->render($template_file);
	}
	
	public function import($template_file)
	{
		$this->display($template_file);
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
		//TODO
		try {
			include $file_name;
		} catch (Exception $e) {
			throw new Kerisy_Exception_500($e->getMessage());
		}
		
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
}