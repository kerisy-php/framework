<?php
/**
 * @brief           Kerisy Framework
 * @author          Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @subpackage      Database
 * @since           2015/11/11
 * @version         2.0.0
 */

namespace Kerisy\Database;

use PDO;
use PDOException;
use Kerisy\Core\Exception;

class Database
{
	// PDO object
	private $_dbh;

	// PDO statement object
	private $_query;

	private $_config = [];

	private $_connected = false;

	// parameters of the SQL query
	private $_parameters = [];

	public function __construct($config = [])
	{
		$this->_config = $config;
	}

	public function connect()
	{
		// PDO dsn
		$dsn = "{$this->_config['driver']}:dbname={$this->_config['database']};host={$this->_config['host']};charset={$this->_config['charset']}";

		try {
			$dbh = new PDO($dsn, $this->_config['user'], $this->_config['password']);
		} catch (PDOException $e) {
			throw new Exception( 'Connection failed: ' . $e->getMessage());
		}

		$this->_dbh = $dbh;
		$this->_parameters = [];
	}

	public function getPrefix()
	{
		return isset($this->_config['prefix']) ? $this->_config['prefix'] : null;
	}

	public function CloseConnection()
	{
		// Set the PDO object to null to close the connection
		// http://www.php.net/manual/en/pdo.connections.php
		$this->_dbh = null;
	}

	private function init($query, $parameters = [])
	{
		// Connect to database
		if(!$this->_connected) { $this->connect(); }
		try {
			// Prepare query
			$this->_query = $this->_dbh->prepare($query);

			// Bind parameters
			foreach($parameters as $k => $v)
			{
				$parameter = ":{$k}";
				var_dump($parameter);
				$this->_query->bindParam($parameter, $v);
			}

			// Execute SQL
			$this->_query->execute();
		}
		catch(PDOException $e)
		{
			throw new Exception( 'Connection failed: ' . $e->getMessage());
		}

		// Reset the parameters
		$this->_parameters = [];
	}

	public function query($query, $params = null, $fetch_mode = PDO::FETCH_ASSOC)
	{
		$query = trim($query);

		$this->init($query, $params);

		$rawStatement = explode(" ", $query);

		// Which SQL statement is used
		$statement = strtoupper($rawStatement[0]);

		if ($statement === 'SELECT	' || $statement === 'SHOW') {
			return $this->_query->fetchAll($fetch_mode);
		}
		elseif ( $statement === 'INSERT' ||  $statement === 'UPDATE' || $statement === 'DELETE' ) {
			return $this->_query->rowCount();
		}
		else {
			return NULL;
		}
	}

	public function lastInsertId() {
		return $this->_dbh->lastInsertId();
	}

	public function column($query, $params = null)
	{
		$this->init($query, $params);
		$Columns = $this->_query->fetchAll(PDO::FETCH_NUM);

		$column = null;

		foreach($Columns as $cells) {
			$column[] = $cells[0];
		}

		return $column;
	}

	public function fetchRow($query, $params = null, $fetch_mode = PDO::FETCH_ASSOC)
	{
		$this->init($query, $params);
		$result = $this->_query->fetch($fetch_mode);
		if($result == false)
		{
			throw new Exception($this->_query->errorInfo()[2]);
		}
		return $result;
	}

	public function single($query, $params = null)
	{
		$this->init($query,$params);
		return $this->_query->fetchColumn();
	}
}
