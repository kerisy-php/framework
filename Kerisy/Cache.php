<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

abstract class Kerisy_Cache
{
	protected $cache_prefix = 'kerisy_cache_';
	protected $cache_group_prefix = 'group_kerisy_cache_';
	
	protected $backend_name = "File";
	protected $frontend_name = 'Core';
	
	protected $cache;
	protected $cache_enabled = true;
	
	protected $_cache_config;
	
	public function __construct()
	{
		$this->_cache_config = Kerisy::config()->get()->cache;
		$this->init($this->_cache_config);
	}
	
	public function init(array $config)
	{
		$this->cache_prefix = $config['prefix'];
		$this->cache_group_prefix = 'group_' . $config['prefix'];
		
		$front_options = array(
						    'caching' => true,
						    'lifetime' => 1800,
						    'automatic_serialization' => true
						);
						
		$backend_options['compression'] = false;

		if ('memcache' == $config['save_handler'])
		{
			$save_handler = 'Memcached';
			$backend_options['servers'] = $config['memcache'];
		}
		else if ('xcache' == $config['save_handler'])
		{
			$save_handler = 'Xcache';
			$backend_options = $config['xcache'];
		}
		else if ('apc' == $config['save_handler'])
		{
			$save_handler = 'Apc';
			$backend_options = $config['apc'];
		}
		else
		{
			$save_handler = $this->backend_name;
			if ('File' == $this->backend_name)
			{
				$backend_options['cache_dir'] = APP_PATH . 'Cache/';
			}
		}
		
		Kerisy::import('Zend.Cache');
		
		$this->cache = Zend_Cache::factory($this->frontend_name, $save_handler, $front_options, $backend_options);
	}
	
	public function get($key)
	{
		if($this->getCacheStatus() === false || !$key)
		{
			return false;
		}	
		
		return $this->getCacheObject()->load($this->getCacheKey($key));
	}
	
	protected function getCacheKey($key)
	{
		return $this->cache_prefix . $key;
	}

	
	protected function getGroupCacheKey($group)
	{
		return $this->cache_group_prefix . $group;
	}
	
	public function getCacheObject()
	{
		return $this->cache;
	}
	
	public function set($key, $data, $group = null, $life_time = 3600 )
	{
		if ($this->getCacheStatus() === false || !$key)
		{
			return false;
		}
		
		$this->getCacheObject()->save($data, $this->getCacheKey($key), array(), $life_time);
		
		if ($group)
		{
			if(is_array($group))
			{
				if(count($group)>0)
				foreach ($group as $cg)
				{
					$this->setGroup($cg, $key);
				}
			}
			else
			{
				$this->setGroup($group, $key);
			}
		}		
	}
	
	protected function setGroup($group, $key)
	{
		if (is_array($group))
		{
			foreach ($group_name as $group)
			{
				$this->setGroup($group, $key);
			}

			return true;
		}

		$group_data = $this->get($this->getGroupCacheKey($group));
		
		//加一个为空判断，防止warning
		if (empty($group_data) || !in_array($key, $group_data))
		{
			$group_data[] = $key;
			
			$this->set($this->getGroupCacheKey($group), $group_data);
		}
	}
	
	public function getGroup($group)
	{
		if ($this->getCacheStatus() === false || !$group)
		{
			return false;
		}
		return $this->get($this->getGroupCacheKey($group));
	}
	
	public function delete($key)
	{
		if ($this->getCacheStatus() === false || !$key)
		{
			return false;
		}
		
		return $this->getCacheObject()->remove($this->getCacheKey($key));
	}
	
	public function cleanGroup($group)
	{
		if ($this->getCacheStatus() === false || !$group)
		{
			return false;
		}
		
		return $this->getCacheObject()->clean(Zend_Cache::CLEANING_MODE_ALL);
	}
	
	public function clean()
	{
		if ($this->getCacheStatus() === false)
		{
			return false;
		}
		
		return $this->getCacheObject()->clean(Zend_Cache::CLEANING_MODE_ALL);
	}
	
	public function setCacheStatus($status = true)
	{
		$this->cache_enabled = $status;
	}
	
	public function getCacheStatus()
	{
		return $this->cache_enabled;
	}
}