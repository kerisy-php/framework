<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

abstract class Kerisy_Model
{
	protected $table_name;
	protected $table_prefix = 'kerisy_';
	protected $primary_key;
	
	protected $_db;

	private static $query_count = 0;
	
	public function getQueries()
	{
		return self::$query_count; 
	}
	
	public function db()
	{
		if (!$this->_db)
		{
			$this->_db = Kerisy::useClass('Kerisy_Db')->master();
		}

		return $this->_db;
	}
	
	public function increaseQuery()
	{
		//echo '<!--', $this->getTableFullName(), '-->';
		self::$query_count++;
	}
	
	public function __construct()
	{
		if (!isset($this->table_name))
		{
			throw new Kerisy_Exception_500('table name should be not null');
		}
		
		if (!isset($this->primary_key))
		{
			throw new Kerisy_Exception_500('primary key should be not null');
		}
		
		$this->_router = Kerisy::loadClass('Kerisy_Router');
	}

	public function createUrl($route, $params = array())
	{
		if (!$this->_router) {
			throw new Kerisy_Exception_500('not set router.');
		}
		
		return $this->base_url . Kerisy::router()->createUrl($route, $params);
	}
	
	protected function getTableFullName()
	{
		if (!isset($this->table_name))
		{
			throw new Kerisy_Exception_500('table name should be not null');
		}
		
		return $this->table_prefix . $this->table_name;
	}

	protected function select()
	{
		return $this->db()->select();
	}
	
	public function insert(array $data)
	{
		$this->master()->insert($this->getTableFullName(), $data);
		
		return $this->master()->lastInsertId($this->getTableFullName(), $this->primary_key);
	}
	
	public function get($primary_key)
	{
		if(!$primary_key)
		{
			return false;
		}
		
		$primary_key = addslashes($primary_key);
		$where = "`{$this->primary_key}` = '{$primary_key}'";

		$select = $this->select();
		$select->from($this->getTableFullName(), '*')
				->where( $where )
				->limit(1, 0);

		return $this->slave()->fetchRow($select);
	}
	
	public function fetch($primary_key)
	{
		$cache_key = 'dbcache_' . $this->getTableFullName() . '_' . $primary_key;
		$data = Kerisy::cache()->get($cache_key);

		if($data !== false)
		{
			return $data;
		}
		
		if (!$data = $this->get($primary_key))
		{
			$data = null;
		}
		
		Kerisy::cache()->set($cache_key, $data);
		
		return $data;
	}
	
	public function fetchAll($limit = 10, $offset = 0 , $where =  null, $order = null,$group =null)
	{
		$select = $this->select();
		$select->from($this->getTableFullName(), '*');
		
		if ($limit)
		{
			$select->limit($limit, $offset);
		}
		
		if($where)
		{
			$select->where($where);
		}
		
		if($order)
		{
			$select->order($order);
		}
		
		if($group)
		{
			$select->group($group);
		}
		//ee($select->__toString());
		
 
		return $this->slave()->fetchAll($select);
	}
	
	public function fetchRow($where  = null, $order = null)
	{		
		$select = $this->select();
		$select->from($this->getTableFullName(), '*');
		
		if($where)
		{
			$select->where($where);
		}
		
		if($order)
		{
			$select->order($order);
		}
		
		$select->limit(1, 0);
		
		return $this->slave()->fetchRow($select);
	}
	
	public function update(array $data, $where = null)
	{
		//ee($select->__toString());
		return $this->master()->update($this->getTableFullName(), $data, $where);
	}
	
	public function delete($primary_key)
	{
		if(!$primary_key)
		{
			return false;
		}
		$where = '1=1 AND ';
		if( is_array( $primary_key ) )
		{
			foreach ($primary_key as $key => $val )
			{
				$where = "`$key` = '$val'";
			}
		}
		else
		{
			$where = $this->primary_key . "='{$primary_key}'";
		}
		
		return $this->master()->delete($this->getTableFullName(), $where );
	}
	
	public function deleteByWhere($where)
	{
		if (!$where)
		{
			return false;
		}
		
		return $this->master()->delete($this->getTableFullName(), $where);
	}
	
	public function listTables()
    {
    	return $this->slave()->listTables();
    }
	
	public function master()
	{
		$this->increaseQuery();
		$this->db = Kerisy::useClass('Kerisy_Db')->master();
		return $this->db();
	}

	public function slave()
	{
		$this->increaseQuery();
		$this->db = Kerisy::useClass('Kerisy_Db')->slave();
		return $this->db();
	}
	
	public function count($where = '')
	{
		$select = $this->select();
		$select->from($this->getTableFullName(), 'COUNT(*) AS n');

		if ($where)
		{
			$select->where($where);
		}

		$row = $this->slave()->fetchRow($select);

		return $row['n'];
	}
	
	public function sum($sum,$where = '')
	{
		$select = $this->select();
		$select->from($this->getTableFullName(), "sum($sum) AS sum");
	
		if ($where)
		{
			$select->where($where);
		}

		$row = $this->slave()->fetchRow($select);
	
		return $row['sum'];
	}
	
	
	public function generateInsertSQL(array $bind)
	{ 
		$cols = array();
        $vals = array();
        $i = 0;
        Kerisy::import('Zend.Db.Expr');
        foreach ($bind as $col => $val) {
            $cols[] = $this->slave()->quoteIdentifier($col, true);
           
            if ($val instanceof Zend_Db_Expr) {
                $vals[] = $val->__toString();
                unset($bind[$col]);
            } else {
                if ($this->slave()->supportsParameters('positional')) {
                    $vals[] = $this->slave()->quoteIdentifier($val, true);
                } else {
                    if ($this->slave()->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':col'.$i] = $val;
                        $vals[] = ':col'.$i;
                        $i++;
                    } else {
        				Kerisy::import('Zend.Db.Adapter.Exception');
                        throw new Zend_Db_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                    }
                }
            }
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->slave()->quoteIdentifier($this->getTableFullName(), true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';
		return $sql;
	}
	
	/* 获取关联表数据
	 $key = $val */
	public function getlink($where) {
		if (!is_array($where))
			return false;

		foreach ($where as $key => $val)
		{
			$where_strarray[]= " $key = $val ";
		}
		$where_str = implode('and',$where_strarray);

		$select = $this->select();
		$select->from( $this->getTableFullName(), '*' )->where($where_str);
		return $this->slave ()->fetchAll ( $select );
	}
}