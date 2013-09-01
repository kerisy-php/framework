<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_View
{
	protected $_template;
	protected $_config = array('template_dir' => 'templates/default/');

	public function init($config = array())
	{
		$this->_config = array_merge($this->_config, $config);
		
		Kerisy::import('Kerisy.Template');
		$this->_template = new Kerisy_Template();
		$this->_template->setTemplateDir($this->_config['template_dir']);
	}

	public function display($template_file)
	{
		$this->_template->display($template_file);
	}

	public function __get($key)
	{
		return $this->_template->$key;
	}

	public function __set($key, $value)
	{
		$this->_template->assign($key, $value);
		return $this;
	}

	public function assign($key, $value = null)
	{
		$this->_template->assign($key, $value);
		return $this;
	}

	public function render($template_file)
	{
		return $this->_template->render($template_file);
	}
}
