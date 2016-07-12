<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/7/2
 */

namespace Kerisy\Job;


class JobBase
{

    // error severity levels
    const CRITICAL = 4;
    const    ERROR = 3;
    const     WARN = 2;
    const     INFO = 1;
    const    DEBUG = 0;
    private static $log_level = self::DEBUG;
    protected static $tableName = "jobs";
    private static $retries = 3; //default retries
    private static $db = null;

    public static function setLogLevel($const) {
        self::$log_level = $const;
    }
    public static function setConnection(\PDO $db) {
        self::$db = $db;
    }

    protected static function getConnection() {
        if (self::$db === null) {
            try {
                $model = new Model();
                self::$db =$model->getDb();
            } catch (\PDOException $e) {
                throw new JobException("Job couldn't connect to the database. PDO said [{$e->getMessage()}]");
            }
        }
        return self::$db;
    }
    
    public static function runQuery($sql, $params = array()) {
        for ($attempts = 0; $attempts < self::$retries; $attempts++) {
            try {
                $stmt = self::getConnection()->prepare($sql);
                $stmt->execute($params);
                $ret = array();
                if ($stmt->rowCount()) {
                    // calling fetchAll on a result set with no rows throws a
                    // "general error" exception
                    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) $ret []= $r;
                }
                $stmt->closeCursor();
                return $ret;
            }
            catch (\PDOException $e) {
                throw $e;
            }
        }
        throw new JobException("Job exhausted retries connecting to database");
    }
    public static function runUpdate($sql, $params = array()) {
//        echo $sql."\r\n";
//        print_r($params);
//        echo "\r\n";
        for ($attempts = 0; $attempts < self::$retries; $attempts++) {
            try {
                $stmt = self::getConnection()->prepare($sql);
                $stmt->execute($params);
                return $stmt->rowCount();
            }
            catch (\PDOException $e) {
                throw $e;
            }
        }
        throw new JobException("Job exhausted retries connecting to database");
    }
    protected static function log($mesg, $severity=self::CRITICAL) {
        if ($severity >= self::$log_level) {
            printf("[%s] %s\n", date('c'), $mesg);
        }
    }
}