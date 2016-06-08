<?php
namespace Kerisy\Database;

/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/3
 * Time: 上午10:19
 */
class PDOConnection extends \PDO implements DriverConnectionInterface
{
    public function __construct($dsn, $username, $passwd, $options = [])
    {
        parent::__construct($dsn, $username, $passwd, $options);
    }


}