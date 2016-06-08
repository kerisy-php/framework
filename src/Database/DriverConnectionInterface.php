<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/5
 * Time: 下午10:30
 */

namespace Kerisy\Database;


interface DriverConnectionInterface
{
    /**
     * Starts a transaction.
     * @return boolean TRUE if the transaction has been started else FALSE.
     */
    public function beginTransaction();

    /**
     * Saves a transaction.
     * @return boolean TRUE if the transaction has been saved else FALSE.
     */
    public function commit();

    /**
     * Cancels a transaction.
     * @return boolean TRUE if the transaction has been canceled else FALSE.
     */
    public function rollBack();

    /**
     * Checks if a transaction has been started.
     * @return boolean TRUE if a transaction has been started else FALSE.
     */
    public function inTransaction();

    /**
     * Quotes a string.
     * @param string $string The string to quote.
     * @param integer $type The PDO type.
     * @return string The quoted string.
     */
    public function quote($string, $type = \PDO::PARAM_STR);

    /**
     * Prepares an SQL statement in order to be executed.
     * @param string $statement The statement to prepare.
     * @return DriverStatementInterface The prepared statement.
     */
    public function prepare($statement);

    /**
     * Executes an SQL statement.
     * @param string $statement The statement to execute.
     * @return integer The number of affected rows.
     */
    public function exec($statement);

    /**
     * Gets the last generated ID or sequence value.
     * @param string $name The name of the sequence object from which the ID should be returned.
     * @return string The last generated ID or sequence value.
     */
    public function lastInsertId($name = null);

    /**
     * Gets the last error code associated with the last operation.
     * @return string The last error code associated with the last operation.
     */
    public function errorCode();

    /**
     * Gets the last error info associated with the last operation.
     * @return string The last error code associated with the last operation.
     */
    public function errorInfo();
}