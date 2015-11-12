<?php

/*
 * This file is part of the Kerisy package.
 *
 * (c) Brian Zou <zoujiaqing@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Kerisy::import('Kerisy.Exception.404');

class Kerisy_Config {

  private static $_config = array();
  private $_base_url;
  private $_module = '';

  public function __construct($module = '') {
	$this->_module = $module;
  }

  public function setModule($module = '') {
	$this->_module = $module;
  }

  public function get($config_filename = 'config', $config_note = 'config') {
	//$config_filename = ucfirst($config_filename);
	$config_folder = 'config';

	$config_key = $this->_module . '_' . $config_filename;

	if (isset(self::$_config[$config_key])) {
	  return self::$_config[$config_key];
	}

	$config_path = '';
	if ($this->_module) {
	  $config_path = 'Modules/' . ucfirst($this->_module) . DS;
	}
	$config_file = '';
	if (DEBUG_MODE && $this->_module == '') {
	  $config_file = $config_path . $config_folder . DS . $config_filename . '.debug.php';
	  if (!is_file(APP_PATH . $config_file)) {
		$config_file = '';
	  }
	}

	if (!$config_file) {
	  $config_file = $config_path . $config_folder . DS . $config_filename . '.php';
	}

	if (!is_file(APP_PATH . $config_file)) {
	  throw new Kerisy_Exception_404('Unable to locate the config file ' . APP_PATH . $config_file);
	} else {
	   include APP_PATH . $config_file;
	}
	self::$_config[$config_key] = (object) $$config_note;
// 		var_dump(self::$_config[$config_key]);
	return self::$_config[$config_key];
  }

}
