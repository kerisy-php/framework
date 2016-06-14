<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/6/5
 * Time: 下午10:12
 */

namespace Kerisy\Database;


interface DriverInterface
{
    public function connect(array $parameters, $username = null, $password = null, array $driverOptions = []);

}