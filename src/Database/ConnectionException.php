<?php
/**
 * Created by PhpStorm.
 * User: haoyanfei
 * Date: 16/7/4
 * Time: 下午5:23
 */

namespace Kerisy\Database;


use Kerisy\Core\Exception;

class ConnectionException extends Exception
{
    /**
     * Gets the "NO ACTIVE TRANSACTION" exception.
     */
    public static function noActiveTransaction()
    {
        return new self('The connection does not have an active transaction.');
    }

    /**
     * Gets the "TRANSACTION ISOLATION NOT SUPPORTED" exception.
     */
    public static function transactionIsolationNotSupported()
    {
        return new self('The connection does not support transaction isolation.');
    }
}