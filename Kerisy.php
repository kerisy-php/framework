<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <brian@kerisy.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

// error_reporting(E_ERROR);
error_reporting(E_ALL^E_NOTICE);

ini_set("error_log", APP_PATH . "logs/errors.log");

date_default_timezone_set('Asia/Shanghai');

define('KERISY_PATH', dirname(__FILE__) . DS);

// 如果没有开启DEBUG模式默认为关闭状态
defined('DEBUG_MODE') || define('DEBUG_MODE', false);

defined('BEGIN_TIME') || define('BEGIN_TIME', microtime(true));

set_include_path(get_include_path() . PS . KERISY_PATH);

require_once KERISY_PATH . 'Function.php';
require_once KERISY_PATH . 'Kerisy/Exception.php';
require_once KERISY_PATH . 'Kerisy/Exception/500.php';

class Kerisy
{
	private static $_version = '1.0.0';
	
	private static $_inited = false;
	
	private static $_classes = array();
	private static $_imports = array();

	private static $_import_prefix = array('App', 'Engine');
	private static $_import_files = array();
	
	private static $_use_classes = array('Kerisy_Router' => 'Kerisy_Router', 'Kerisy_Cache' => 'Kerisy_Cache');
    public static $_mcaId = 'index_index_index';
	public static function init()
	{
		if (true == function_exists("set_time_limit") AND @ini_get("safe_mode") == 0)
		{
			@set_time_limit(10);
		}
		
		if (get_magic_quotes_gpc())
		{
		    function stripslashes_gpc(&$value)
		    {
		        $value = stripslashes($value);
		    }
		
		    array_walk_recursive($_GET, 'stripslashes_gpc');
		    array_walk_recursive($_POST, 'stripslashes_gpc');
		    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
		    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
		}
		
		if (@ini_get('register_globals'))
		{
			foreach ($_REQUEST as $name => $value)
			{
				unset($$name);
			}
		}
		
		// Kerisy error exception
		set_exception_handler(array('Kerisy_Exception', 'handler'));
		
		if ($import_prefix = Kerisy::config()->get()->import_prefix)
		{
			self::setImportPrefix($import_prefix);
		}
		
		if ($use_classes = Kerisy::config()->get()->use_classes)
		{
			self::setUseClasses($use_classes);
		}

		self::$_inited = true;
	}
	
	public static function setImportPrefix($import_prefix = array())
	{
		self::$_import_prefix = $import_prefix;
	}
	
	public static function setUseClasses($use_classes = array())
	{
		self::$_use_classes = array_merge(self::$_use_classes, $use_classes);
	}
	
	public static function loadClass($class_name)
	{
		$class_name = isset(self::$_use_classes[$class_name]) ? self::$_use_classes[$class_name] : $class_name;
		if (isset(self::$_classes[$class_name]))
		{
			return self::$_classes[$class_name];
		}
		self::import(str_replace('_', '.', $class_name));
		self::$_classes[$class_name] = new $class_name;
		return self::$_classes[$class_name];
	}
	
	public static function useClass($class_name)
	{
		return self::loadClass($class_name);
	}
	
	public static function createApplication($application_mode = 'web')
	{
		if (false == self::$_inited)
		{
			throw new Kerisy_Exception_500("Kerisy framework is not inited.");
		}

		if (!in_array($application_mode, array('web', 'console')))
    	{
    		throw new Kerisy_Exception_500("Application mode '{$application_mode}' don't supported.");    		
    	}
    	
    	self::import('Kerisy.Application.' . ucfirst($application_mode));

		$class_name = 'Kerisy_' . 'Application_' . ucfirst($application_mode);

		return new $class_name();
	}
	
	// 直接用 $in_app 传值的方式来指定库文件位置
	public static function import($import_string, $in_app = false)
	{
		if (isset(self::$_import_files[$import_string]))
		{
			return ;
		}
		
		if (preg_match('/^([a-z0-9]+)\./i', $import_string, $matches))
		{
			$dir_prefix = $matches[1];
		}

		if ($in_app || in_array($dir_prefix, self::$_import_prefix))
		{
			$parent_dir =  in_array($dir_prefix, self::$_import_prefix) ? 'Library' : 'Modules';
			$import_file = APP_PATH . $parent_dir . DS . str_replace('.', DS, $import_string) . '.php';
	
		}
		else
		{
			$import_file = KERISY_PATH . str_replace('.', DS, $import_string) . '.php';
		}
		
		//if (is_file($import_file)) 
		//{
			require_once $import_file;
			self::$_import_files[$import_string] = $import_file;
		//}
	}
	
	public static function config($module = '')
	{
		$config_class_key = 'Kerisy_Config_' . $module;
		if (isset(self::$_classes[$config_class_key]))
    	{
    		return self::$_classes[$config_class_key];
    	}
    	self::import('Kerisy.Config');
    	self::$_classes[$config_class_key] = new Kerisy_Config($module);
    	
		return self::$_classes[$config_class_key];
	}
	
	public static function router()
	{
		return self::loadClass('Kerisy_Router');
	}
	
	public static function cache()
	{
		return self::loadClass('Kerisy_Cache');
	}
	
	public static function i18n()
	{
		return self::loadClass('Kerisy_I18n');
	}
	
	public static function model($class_name)
	{
		return self::loadModuleClass($class_name);
	}
	
	public static function db()
	{
		$class_name = 'Kerisy_Db';
		return self::loadClass($class_name);
	}
	
	public static function crond($crond_name)
	{
		return self::loadModuleClass($crond_name);
	}
	
	public static function redis()
	{
		$class_name = 'Kerisy_Redis';
		return self::loadClass($class_name);
	}

	public static function helper($class_name)
	{
		return self::loadModuleClass($class_name);
	}
	
	public static function module($class_name)
	{
		return self::loadModuleClass($class_name);
	}
	
	public static function controller($class_name)
	{
		return self::loadModuleClass($class_name);
	}
	
	public static function loadModuleClass($class_name)
    {
    	if (isset(self::$_classes[$class_name]))
    	{
    		return self::$_classes[$class_name];
    	}

    	$class_filename = APP_PATH . 'Modules/' . str_replace('_', '/', $class_name) . '.php';

    	if (!is_file($class_filename))
    	{
    		throw new Kerisy_Exception_500('Unable to load file: ' . $class_filename);
    	}
    	
    	require_once $class_filename;
    	
    	try {
    		self::$_classes[$class_name] = new $class_name;
    	} catch (Exception $e) {
    		throw new Kerisy_Exception_500($e->getMessage());
    	}
    		
    	return self::$_classes[$class_name];
    }
	
	public static function version()
	{
		return self::$_version;
	}
}