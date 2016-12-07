<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Storage;

use Config;
use Kerisy\Coroutine\Db\MysqlAsync as PMysqlAsync;
use Kerisy\Coroutine\Db\DbCoroutine;
use Kerisy\Foundation\Storage\Adapter\SQlAbstract as SQlAdapter;

class Mysql extends SQlAdapter
{
    private  static $coroutine = null;

    public function __construct($storageConfig=null)
    {
        if(!self::$coroutine){
            if(!$storageConfig){
                $storageConfig = Config::get("storage.server.pdo");
            }
            self::$prefix = $storageConfig['prefix'];
            parent::__construct();
            $pdoPool = new PMysqlAsync($storageConfig);
            self::$coroutine = new DbCoroutine($pdoPool);
        }

    }

    /**
     *
     * 更新，插入，删除sql执行,只返回受影响的行数
     * @param unknown_type $sql
     * @param unknown_type $data
     */
    public function exec($sql, $connType = self::CONN_MASTER, $isInsert=false)
    {
        if (!$sql) {
            return false;
        }

        if (!(strtolower(substr($sql, 0, 6)) == 'insert' || strtolower(substr($sql, 0, 4)) == 'update'
            || strtolower(substr($sql, 0, 4)) == 'drop' || strtolower(substr($sql, 0, 4)) == 'delete'
            || strtolower(substr($sql, 0, 4)) == 'create'
            || strtolower(substr($sql, 0, 5)) == 'begin'
            || strtolower(substr($sql, 0, 6)) == 'commit'
            || strtolower(substr($sql, 0, 8)) == 'rollback'
        )
        ) {
            throw new \Exception("only run on select , show");
        }

        self::$_sql['sql'] = $sql;
        $func = $isInsert?"lastInsertId":"";
        yield self::$coroutine->set($sql, $connType, $func);
    }


    public function fetchAll($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetchAll";
        yield self::$coroutine->set($sql, $connType, $func);
    }


    public function fetch($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetch";
        yield self::$coroutine->set($sql, $connType, $func);
    }
    
    /************携程方法处理**********************/

    /**
     * 根据条件获取多个
     * @param array $where
     */
    public function gets($where = array(), $orderBy = "", $limit = "", $offset = "", $groupBy = "", $returnCount = false, $tableName = null)
    {
        $whereSql = $this->parseWhere($where);

        $whereSql = $whereSql ? " WHERE " . $whereSql : "";
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();
        $orderBySql = "";
        $groupBySql = "";
        $limitSql = "";
        if ($groupBy) {
            $groupBySql = " GROUP BY " . $groupBy;
        }
        if ($orderBy) {
            $orderBySql = " ORDER BY " . $orderBy;
        }

        if ($limit) {
            $limit = intval($limit);
            $offset = intval($offset);
            $limitSql = " LIMIT {$offset} , {$limit} ";
        }

        $field = $this->field?$this->field:"*";

        $sql = "SELECT {$field}  FROM `{$tableName}` " . $whereSql . $groupBySql . $orderBySql . $limitSql;

        $this->field = null;
        $rs = $this->fetchAll($sql, self::CONN_SLAVE);
        if ($returnCount) {
            $sqlCount = "SELECT count(*) as cnt FROM  `{$tableName}` " . $whereSql;
            $rsCount = $this->fetch($sqlCount, self::CONN_SLAVE);
            if ($rsCount instanceof \Generator) {
                $rsCount = yield $rsCount;
            }
            $this->_total = $rsCount['cnt'];
        }

        if ($rs instanceof \Generator) {
            $rs = yield $rs;
        }

        return $rs;
    }

    /**
     * 得到单个字段
     * @param  [type]  $field   [description]
     * @param  [type]  $where   [description]
     * @param  boolean $isMore [description]
     * @param  string $orderBy [description]
     * @param  string $limit [description]
     * @param  string $offset [description]
     * @param  string $groupBy [description]
     * @return [type]           [description]
     */
    public function getField($field, $where, $isMore = false, $resultKey = "", $orderBy = "", $limit = "", $offset = "", $groupBy = "", $tableName = null)
    {
        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();
        $orderBySql = "";
        $groupBySql = "";
        $limitSql = "";
        if ($groupBy) {
            $groupBySql = " GROUP BY " . $groupBy;
        }
        if ($orderBy) {
            $orderBySql = " ORDER BY " . $orderBy;
        }

        if ($limit) {
            $limit = intval($limit);
            $offset = intval($offset);
            $limitSql = " LIMIT {$offset} , {$limit} ";
        }
        $resultKeyStr = $resultKey ? $resultKey . "," : "";
        $sql = "SELECT " . $resultKeyStr . " " . $field . "  FROM `{$tableName}` " . $whereSql . $groupBySql . $orderBySql . $limitSql;

        if ($isMore) {
            $rs = $this->fetchAll($sql, self::CONN_SLAVE);
            if ($rs instanceof \Generator) {
                $rs = yield $rs;
            }
            if (!$rs) {
                return array();
            }
            $result = array();

            foreach ($rs as $key => $value) {
                if(isset($value[$resultKey])){
                    $result[$value[$resultKey]] = $value[$field];
                }else{
                    $result[] = $value[$field];
                }
            }
            return $result;
        } else {
            $rs = yield $this->fetch($sql, self::CONN_SLAVE);
            $result = $rs ? $rs[$field]: null;
            return $result;
        }
    }


    /**
     * 获取分页数据
     * @param array $where
     * @param int $page
     * @param int $pageSize
     * @param string $orderBy
     * @param string $groupBy
     * @return array
     */
    function pager($where = array(), $page=1,$pageSize=20, $orderBy = "", $groupBy = ""){
        $page = $page?$page:1;
        $limit = ($page-1)*$pageSize;
        $list = $this->gets($where,$orderBy,$pageSize,$limit,$groupBy,true);
        if ($list instanceof \Generator) {
            $list = yield $list;
        }
        $count = $this->getTotal();
        $totalPage = $count==0?0:ceil($count/$pageSize);
        return array($list,$count, $totalPage);
    }

    /**
     * 获取分页数据
     * @param $sql
     * @param int $page
     * @param int $pageSize
     * @return array
     * @throws \Exception
     */
    function pagerSql($sql,$page=1,$pageSize=20){
        $page = $page?$page:1;
        $limit = ($page-1)*$pageSize;
        $sql .= " LIMIT ".$limit.", ".$pageSize;
        $list = $this->selectAll($sql,array(),true);
        if ($list instanceof \Generator) {
            $list = yield $list;
        }
        $count = $this->getTotal();
        $totalPage = $count==0?0:ceil($count/$pageSize);
        return array($list,$count, $totalPage);
    }

    /**
     *
     * 获取数据行数
     * @param array $where
     */
    public function getCount($where = array(), $groupBy = "", $tableName = null)
    {
        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();
        $groupBySql = "";
        if ($groupBy) {
            $groupBySql = " GROUP BY " . $groupBy;
        }
        $sql = "SELECT count(*) as cnt FROM `{$tableName}` " . $whereSql . $groupBySql;
        $return = $this->fetch($sql, self::CONN_SLAVE);
        if ($return instanceof \Generator) {
            $return = yield $return;
        }
        return $return['cnt'];
    }


    /**
     *
     * 用sql语句获取所有
     * @param string $sql
     * @param array $data
     * @param $returnCount //SQL_CALC_FOUND_ROWS
     */
    public function selectAll($sql, $data = array(), $returnCount = false)
    {
        if (empty($sql)) {
            return false;
        }

        if (!(strtolower(substr($sql, 0, 6)) == 'select' || strtolower(substr($sql, 0, 4)) == 'show')) {
            throw new \Exception("coan not run on insert ,update ,delete");
        }
        if ($data) {
            foreach ($data as $k => $v) {
                $v = $v === null ? "null" : $this->quote($v);
                $sql = str_replace(":" . $k, $v, $sql);
            }
        }
//
        $rs = $this->fetchAll($sql, self::CONN_SLAVE);
        if ($returnCount) {
            $sqlCount = 'SELECT FOUND_ROWS() as cnt';
            $rsCount = $this->fetch($sqlCount, self::CONN_SLAVE);
            if ($rsCount instanceof \Generator) {
                $rsCount = yield $rsCount;
            }
            $this->_total = $rsCount['cnt'];
        }
        if ($rs instanceof \Generator) {
            $rs = yield $rs;
        }
        return $rs;
    }
    

}