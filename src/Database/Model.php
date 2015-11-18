<?php
/**
 * @project            Kerisy Framework
 * @author             Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright         (c) 2015 putao.com, Inc.
 * @package            kerisy/framework
 * @create             2015/11/11
 * @version            2.0.0
 */

namespace Kerisy\Database;

use Kerisy\Core\Exception;

class Model
{
	private static $_db;
	private $_data = [];

	public function __construct($data = array()) {
		if (!self::$_db)
		{
			$config = config('database')->get(KERISY_ENV);
			self::$_db = new Database($config);
		}

		$this->_data = $data;
	}

	public static function db()
	{
		return self::$_db;
	}

	public function getTable()
	{
		return Model::db()->getPrefix() . $this->table;
	}

	public function __set($name,$value){
		if(strtolower($name) === $this->primaryKey)
		{
			$this->_data[$this->primaryKey] = $value;
		}
		else
		{
			$this->_data[$name] = $value;
		}
	}

	public function __get($name)
	{
		if(is_array($this->_data)) {
			if(array_key_exists($name,$this->_data)) {
				return $this->_data[$name];
			}
		}

		$trace = debug_backtrace();

		throw new Exception('Undefined property via __get(): ' . $name .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line']);

		return null;
	}

	public function save($id = "0") {
		$this->_data[$this->primaryKey] = (empty($this->_data[$this->primaryKey])) ? $id : $this->_data[$this->primaryKey];

		$fieldsvals = '';
		$columns = array_keys($this->_data);

		foreach($columns as $column)
		{
			if($column !== $this->primaryKey)
			$fieldsvals .= $column . " = :". $column . ",";
		}

		$fieldsvals = substr_replace($fieldsvals , '', -1);

		if(count($columns) > 1 ) {
			$sql = "UPDATE " . $this->getTable() .  " SET " . $fieldsvals . " WHERE " . $this->primaryKey . "= :" . $this->primaryKey;
			return Model::db()->query($sql,$this->_data);
		}
	}

	public function create() {
		$bindings   	= $this->_data;

		if(!empty($bindings)) {
			$fields     =  array_keys($bindings);
			$fieldsvals =  array(implode(",",$fields),":" . implode(",:",$fields));
			$sql 		= "INSERT INTO ".$this->getTable()." (".$fieldsvals[0].") VALUES (".$fieldsvals[1].")";
		}
		else {
			$sql 		= "INSERT INTO ".$this->getTable()." () VALUES ()";
		}

		return Model::db()->query($sql,$bindings);
	}

	public function delete($id = "") {
		$id = (empty($this->_data[$this->primaryKey])) ? $id : $this->_data[$this->primaryKey];

		if(!empty($id)) {
			$sql = "DELETE FROM " . $this->getTable() . " WHERE " . $this->primaryKey . "= :" . $this->primaryKey. " LIMIT 1" ;
			return Model::db()->query($sql, [$this->primaryKey => $id]);
		}
	}

	public function find($id = "") {
		$id = (empty($this->_data[$this->primaryKey])) ? $id : $this->_data[$this->primaryKey];

		if(!empty($id))
		{
			$sql = "SELECT * FROM " . $this->getTable() ." WHERE " . $this->primaryKey . "= :" . $this->primaryKey . " LIMIT 1";

			return ($this->_data = Model::db()->fetchRow($sql, [$this->primaryKey => $id])) ? true : false;
		}

		return false;
	}

	public function all(){
		return Model::db()->query("SELECT * FROM " . $this->getTable());
	}
	
	public function min($field)  {
		if($field)
		return Model::db()->single("SELECT min(" . $field . ")" . " FROM " . $this->getTable());
	}

	public function max($field)  {
		if($field)
		return Model::db()->single("SELECT max(" . $field . ")" . " FROM " . $this->getTable());
	}

	public function avg($field)  {
		if($field)
		return Model::db()->single("SELECT avg(" . $field . ")" . " FROM " . $this->getTable());
	}

	public function sum($field)  {
		if($field)
		return Model::db()->single("SELECT sum(" . $field . ")" . " FROM " . $this->getTable());
	}

	public function count($field)  {
		if($field)
		return Model::db()->single("SELECT count(" . $field . ")" . " FROM " . $this->getTable());
	}
}
