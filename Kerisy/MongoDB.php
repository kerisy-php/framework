<?php

class Kerisy_MongoDB
{
	public static $_db;
	public static $_mongoDB;

	public function __construct($config = array())
	{
		if (empty($config)) {
			ee('mongo config error');
		}
		$m = new MongoClient($config['connectionString']);
		static::$_db = $m->selectDB($config['dbName']);
	}

	public static function findAll($argv = array(), $fields = array(), $sort = array())
	{

		// $argv = self::validate($argv);
		if ($argv) {
			$result = self::$_mongoDB->find($argv, $fields)
			 ->sort($sort);
			return self::toArray($result);
		}
		return FALSE;
	}

	public static function find($argv = array(), $skip = 0, $limit = 30, $sort = array(), $fields = array()) {

		//$argv = self::validate($argv);
		if ($argv) {
			$result = self::$_mongoDB->find($argv, $fields)
			 ->skip($skip)
			 ->limit($limit)
			 ->sort($sort);
			return self::toArray($result);
		}
		return array();
	}

	/**
	 * Count
	 *
	 * @param unknown $argv
	 */
	public static function count($argv = array())
	{
		// $argv = self::validate($argv);
		return self::$_mongoDB->count($argv);
	}

	public function aggregate($ops)
	{
		return self::$_mongoDB->aggregate($ops);
	}

	/**
	 *
	 * Distinct
	 * @author hof <haoyanfei@outlook.com>
	 * @param type $argv
	 * @return type
	 */
	public static function distinct($key, $argv = array())
	{
		// $argv = self::validate($argv);
		return self::$_mongoDB->distinct($key, $argv);
	}

	/**
	 *
	 * @param type $keys
	 * @param type $initial
	 * @param type $reduce
	 * @return type
	 */
	public function group($keys, $initial, $reduce, $condition)
	{
		return self::$_mongoDB->group($keys, $initial, $reduce, $condition);
	}

	/**
	 * Mongodb Object To Array
	 *
	 * @param array $data
	 * @return multitype:
	 */
	private static function toArray($data)
	{
		return self::cleanId(iterator_to_array($data));
	}

	/**
	 * Clear Mongo _id
	 *
	 * @param array $data
	 * @return void unknown
	 */
	private static function cleanId($data)
	{
		$s = '$id';
		if (isset($data['_id'])) {
			$data['_id'] = $data['_id']->$s;
			return $data;
		} elseif ($data) {
			foreach ($data as $key => $value) {
				$data[$key]['_id'] = $value['_id']->$s;
			}
		}
		return $data;
	}

	/**
	 * Validate Data Callbak Function
	 *
	 * @param array $argv
	 */
	private static function validate($data) {
		if (static::$_validate) {
			foreach (static::$_validate as $arg => $validate) {
				if (is_array($data) && array_key_exists(strval($arg), $data)) {
					foreach ($validate as $key => $value) {
						switch (strtolower($key)) {
							case 'type':
								if ($value == 'int') {
									$data[$arg] = (int) $data[$arg];
								} elseif ($value == 'string') {
									$data[$arg] = (string) $data[$arg];
								} elseif ($value == 'bool') {
									$data[$arg] = (bool) $data[$arg];
								} elseif ($value == 'float') {
									$data[$arg] = (float) $data[$arg];
								} elseif ($value == 'array') {
									$data[$arg] = (array) $data[$arg];
								}
								break;
							case 'min':
								if (strlen($data[$arg]) < $value) {
									exit('Error: The length of ' . $arg . ' is not matched');
								}
								break;
							case 'max':
								if (strlen($data[$arg]) > $value) {
									exit('Error: The length of ' . $arg . ' is not matched');
								}
								break;
							case 'func':
								$call = preg_split('/[\:]+|\-\>/i', $value);
								if (count($call) == 1) {
									$data[$arg] = call_user_func($call['0'], $data[$arg]);
								} else {
									$data[$arg] = call_user_func_array(array(
									 $call['0'],
									 $call['1']
									), array(
									 $data[$arg]
									));
								}
								break;
						}
					}
				}
			}
		}
		return $data;
	}
}
