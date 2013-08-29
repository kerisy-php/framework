<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

Kerisy::import('Zend.Db');
Kerisy::import('Zend.Registry');
Kerisy::import('Zend.Db.Table.Abstract');

class Kerisy_Db
{
	private $_config = array();
	private $_drivers = array();
	private $_current_db_driver = 'master';
	
	public function __construct()
	{
		$this->_config = Kerisy::config()->get()->database;
	}
	
	private function connect($driver)
	{
		if (!array_key_exists($driver, $this->_config))
		{
			return false;
		}

		try
		{
			$this->_drivers[$driver] = Zend_Db::factory($this->_config['driver'], $this->_config[$driver]);
			$charset = $this->_config['charset'] ? $this->_config['charset'] : 'utf8';
			$this->_drivers[$driver]->query("SET NAMES {$this->_config['charset']}");

			Zend_Registry::set('dbAdapter', $this->_drivers[$driver]);
			
			return $this->_drivers[$driver];
		}
		catch (Exception $e)
		{
			throw new Kerisy_Exception_500($e->getMessage());
		}
	}
	
	public function useDb($driver)
	{
		if ($driver && isset($this->_drivers[$driver]))
		{
			$this->_current_db_driver = $driver;
			Zend_Db_Table_Abstract::setDefaultAdapter($this->_drivers[$driver]);
			return $this->_drivers[$driver];
		}

		return $this->connect($driver);
	}
	
	public function getDb()
	{
		if (!count($this->_drivers))
		{
			throw new Kerisy_Exception_500('No database connection object!');
		}
		
		return $this->_drivers[$_current_db_driver];
	}
	
	public function __destruct()
	{
		$this->_drivers = null;
	}
}
