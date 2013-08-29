<?php

/*
* This file is part of the Kerisy package.
*
* (c) Lei Dong <xiaosan@outlook.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

/**
 * 具体方法说明参照：
 * https://github.com/nicolasff/phpredis
 * 使用方法：
 * $redis = new Core_Redis();
 * $redis = $redis->accessedtable(table_name);//  此部分省略后会默认选择0号数据库
 * 
 */
class Kerisy_Redis
{
	
	protected $_host = '192.168.32.8';
	protected $_port = 6379;
	protected $_password = '123456';
	protected $_dbindex = 0;
	protected $_prefix = 0;
	
	protected $_auth = false;
	protected $_redis ;
	protected $_redis_group_key = 'r_g_';
	protected $_server_id;
	protected $_redis_client = 'extension';
	
	public function __construct()
	{
		$redis_config = Kerisy::config()->get()->redis;
		
		$this->_host = $redis_config['host'];
		$this->_port = $redis_config['port'];
		$this->_password = $redis_config['password'];
		$this->_dbindex = $redis_config['dbindex'];
		$this->_prefix = $redis_config['prefix'];
		$this->_server_id = $redis_config['server_id'];
		if ($redis_config['redis_client']) {
			$this->_redis_client = $redis_config['redis_client'];
		}
		
		
		if ($this->_redis_client == 'predis') {
			require __DIR__.'/Predis.php';
			$this->_redis = new Predis_Client(array('host'=> $this->_host, 'port' =>$this->_port, 'database' =>$this->_dbindex));
		}
		elseif ($this->_redis_client == 'extension')
		{
			
			$this->_redis = new Redis();
			$this->_redis->connect($this->_host, $this->_port);
		}
		
		
		
		$this->select();
		
	}
	
	public function __destruct()
	{
		//用于关闭redis链接
		//$this->_redis->close();
	}
	 
	
	public function setDbIndex($dbindex)
	{
		$this->_dbindex = $dbindex;
		return true;
	}
	
	public function setPrefix($prefix)
	{
		$this->_prefix = $prefix;
		return true;
	}

	public function setTimeout($key, $integer)
	{
		$saveKey = $this->getSaveRedisKey($key);
		
		return $this->_redis->expire($saveKey, $integer); 
	}
	public function getRedisObject()
	{
		return $this->_redis;
	}
	
	//获得保存时候的key
	public function getSaveRedisKey($key)
	{
		return $this->_prefix . $this->_server_id . $key;
	}
	
	
	private function auth()
	{
		return $this->_redis->auth($this->_password);
	}
	
	public function get($key)
	{
		$result = null;
		if($key!=null)
		{
			$key = $this->getSaveRedisKey($key);

			if($result = $this->_redis->get($key))
			{
				$result = unserialize($result);
			}
		}
		return $result;
	}
	
	public function set($key, $value, $group_key = null,$second = null)
	{
		$result = false;
		if($key!=null && $value!=null)
		{
			$value = serialize($value);
			$save_key = $this->getSaveRedisKey($key);
			
			if($second)
				$result = $this->_redis->setex($save_key,$second,$value);
			else
				$result = $this->_redis->set($save_key,$value);
			
			if($group_key)
			{
				$this->setGroup($group_key, $key);
			}
			
		}
		return $result;
	}
	
	public function decr($key)
	{
		$result = '';
		if($key)
		{
			$key = $this->getSaveRedisKey($key);
			$this->_redis->decr($key);
		}
		return $result;
	}
	
	/**
	 * set a value and return the previous entry at that key
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @param unknown_type $group_key
	 */
	public function getSet($key, $value, $group_key = null)
	{
		$result = false;
		if($key && $value)
		{
			$value = serialize($value);
			$save_key = $this->getSaveRedisKey($key);
			
			$result = $this->_redis->getSet($save_key, $value);
			
			if($group_key)
			{
				$this->setGroup($group_key, $key);
			}
			
		}
		return $result;
	}
	
	public function delete($keys)
	{
		if(is_array($keys))
		{
			foreach ($keys as $key)
			{
				$this->delete($key);
			}
		}
		else
		{
			$keys = $this->getSaveRedisKey($keys);
			
			return $this->_redis->del($keys);
		}
	} 
	
	public function setnx($key, $value)
	{
		$result = false;
		
		if($keys)
		{
			$result = $this->_redis->delete($keys);
		}
		return  $result;
	}
	
	public function getKeys($filter)
	{
		if(!$filter)
			return ;
		$filter = $this->getSaveRedisKey($filter);
		return $this->_redis->keys($filter);
	}
	
	public function getValues($filter)
	{
		if(!$filter)
			return false;
		$keys = $this->getKeys($filter);
		if (empty($keys))return false;
		
		$start = strlen($this->_prefix . $this->_server_id );
		$data = array();
		foreach ($keys as $key)
		{
			$k = substr($key, $start);
			$data[$k] = $this->get($k);
		}
		return $data;
	}
	
	public function exists($key)
	{
		if(!$key)
		{
			return false;
		}
		
		$key = $this->getSaveRedisKey($key);
		
		return $this->_redis->exists($key);
	}
	
	public function rename( $key, $newKey )
	{
		return $this->_redis->RENAME( $key, $newKey );
	}
	/**
	 * 如果key 不存在则从1000开始
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public function incrBy($key,$value = 1)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->incrBy($save_key, $value);
	}

    public function getIncr( $key )
    {
        $save_key = $this->getSaveRedisKey($key);
        return $this->_redis->get($save_key);
    }

    public function setIncr( $key ,$val)
    {
        $save_key = $this->getSaveRedisKey($key);
        return $this->_redis->set($save_key,$val);
    }
	
	public function save()
	{
		return $this->_redis->save();
	}
	
	public function bgSave()
	{
		return $this->_redis->bgsave();
	}
	
	//Returns the timestamp of the last disk save.
	//return INT: timestamp
	public function lastSave()
	{
		return $this->_redis->lastSave();
	}
	
	public function flushDB()
	{
		return $this->_redis->flushDB();
	}
	
	public function info()
	{
		return $this->_redis->info();
	}
	
	public function script($script)
	{
		ee('script fobbiden');
		if($script)
		{
			return $this->_redis->script('load', $script);
		}		
		return false;
	}
	
	
	//$kv = array('k1'=>'v1','k2'=>'v2')
	public function mset($kv)
	{
		if(!is_array($kv))
		{
			return false;
		}
		return $this->_redis->mset();
	}
	
	protected function select()
	{
		return $this->_redis->select($this->_dbindex);
	}
	
	public function setGroup($group_key, $redis_key)
	{
		if (is_array($group_key))
		{
			foreach ($group_key as $gkey) 
			{
				$this->setGroup($gkey, $redis_key);
			}
		}
		else 
		{
			$group_key_full =  $this->_redis_group_key . $group_key;
			
			$data = $this->get($group_key_full);
			if($data)
			{
				if(in_array($redis_key, $data))
				{
					return true;
				}
				$data[] = $redis_key;
			}
			else
			{
				$data = array($redis_key);
			}	
					
			$this->set($group_key_full, $data);
			
			return true;
		}
	}
	
	public function delGroup($group_key)
	{
		if (is_array($group_key))
		{
			foreach ($group_key as $gkey) 
			{
				$this->delGroup($gkey);
			}
		}
		else 
		{
			$group_key_full = $this->_redis_group_key . $group_key;
			
			$data = $this->get($group_key_full);
			
			if($data)
			{
				$this->delete($data);
			}
			
			$this->delete($group_key_full);
			
			return true;
		}
	}
	
	public function getGroup($group_key)
	{
		$group_key_full = $this->_redis_group_key . $group_key;
		
		return $this->get($group_key_full);
	}
	
	
	/*---------------------hash table-------------------------------*/
	
	public function hSet($key, $hashKey, $value)
	{
		$save_key = $this->getSaveRedisKey($key);
		$value = serialize($value);
		return $this->_redis->hSet($save_key, $hashKey, $value);
	}
	
	public function hExists($key, $hashKey)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->hExists($save_key, $hashKey);
	}
	
	public function hGet($key, $hashKey)
	{
		$save_key = $this->getSaveRedisKey($key);
		if(false !== $data = $this->_redis->hGet($save_key, $hashKey))
		{
			return unserialize($data);
		}
		return false;
	}

	public function hKeys($key)
	{
		$save_key = $this->getSaveRedisKey($key);		
		return $this->_redis->hKeys($save_key);
	}
	
	public function hGetAll($key)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_unserialize($this->_redis->hGetAll($save_key));
	}
	
	public function hDel($key, $hashKey)
	{
		$save_key = $this->getSaveRedisKey($key);		
		return $this->_redis->hDel($save_key, $hashKey);
	}

    public function hIncrBy( $key , $hashKey , $inc = 1 )
    {
        $save_key = $this->getSaveRedisKey($key);
        //不要用exists判断
        return $this->_redis->hIncrBy( $save_key , $hashKey , $inc );
    }
    public function hIncrGetAll( $key )
    {
        $save_key = $this->getSaveRedisKey($key);
        return $this->_redis->hGetAll($save_key);
    }
    public function hIncrGet( $key , $hashKey )
    {
        $save_key = $this->getSaveRedisKey($key);
        return $this->_redis->hGet($save_key, $hashKey);
    }
    public function hIncrSet( $key , $hashKey ,$val)
    {
        $save_key = $this->getSaveRedisKey($key);
        return $this->_redis->hSet($save_key, $hashKey,$val);
    }

	/*---------------------list-------------------------------*/
	
	public function rPush($key, $value)
	{
		$save_key = $this->getSaveRedisKey($key);
		$value = serialize($value);
		return $this->_redis->rPush($save_key, $value);
	}
	
	public function lPush($key, $value)
	{
		$save_key = $this->getSaveRedisKey($key);
		$value = serialize($value);
		return $this->_redis->lPush($save_key, $value);
	}

	public function lGet($key, $index)
	{
		$save_key = $this->getSaveRedisKey($key);
		if(false !== $data =  $this->_redis->lIndex($save_key, $index))
		{
			return unserialize($data);
		}
		return false;
	}
	
	public function lRange($key, $start, $end)
	{
		$save_key = $this->getSaveRedisKey($key);
		$data = $this->_redis->lRange($save_key, $start, $end);
		if (empty($data)) {
			return false;
		}
		return $this->_unserialize($data);
	}
	
	public function lSize($key)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->lLen($save_key);
	}
	
	/**
	 * 
	 * @param unknown_type $key
	 * @param int $count -1 return all, 0  this param is not valiate, other int is need len
	 */
	public function lPop($key, $count = 0)
	{
		$save_key = $this->getSaveRedisKey($key);
		if(0 === $count)
		{
			if (false !== $data = $this->_redis->lPop($save_key))
				$data = unserialize($data);
			return $data;
		}
		if (-1 === $count)
		{
			$tmp = array();
			while (false !== $data = $this->_redis->lPop($save_key))
			{
				$tmp[] = $data;
			}
			return  empty($tmp) ? false : $this->_unserialize($tmp);
		}
		
		$i = 0;
		$tmp = array();
		while ($i < $count && false !== $data = $this->_redis->lPop($save_key))
		{
			$tmp[] = $data;
			$i++;
		}
		return  empty($tmp) ? false : $this->_unserialize($tmp);
	}
	
	private function _unserialize($array_ser)
	{
		if (is_array($array_ser))
		{
			$tmp = array();
			foreach ($array_ser as $key => $value) {
				$tmp[$key] = unserialize($value);
			}
			return $tmp;
		}
		return false;
	}
	
	/*---------------------sets-------------------------------*/
	
	public function sAdd($key, $value)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->sAdd($save_key , $value);
	}

	
	public function sIsMember($key, $value)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->sIsMember($save_key , $value);
	}
	
	public function sMembers($key)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->sMembers($save_key);
	}

	
	public function sRemove($key, $value)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->sRem($save_key, $value);
	}
	
	public function sSize($key)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->sCard($save_key);
	}
	public function sCard($key)
	{
		$save_key = $this->getSaveRedisKey($key);
		return $this->_redis->sCard($save_key);
	}
}