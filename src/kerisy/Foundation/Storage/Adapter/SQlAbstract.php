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

namespace Kerisy\Foundation\Storage\Adapter;

abstract class SQlAbstract
{
    protected $hasTran = 0;//是否有事务
    protected $_total = 0;
    protected static $_sql = array();
    protected static $sqlinstance = array();
    protected $tableName = null;
    protected $prefix = "";

    const CONN_MASTER = 0;
    const CONN_SLAVE = 1;

    public abstract function fetch($sql, $connType);

    public abstract function fetchAll($sql, $connType);

    public abstract function exec($sql, $connType);

    public function __construct()
    {

    }


    public function getTableName()
    {
        $className = get_called_class();
        $obj = new $className;
        $table = $obj->tableName;
        $table = $this->prefix.$table;
        return $this->tableName = $table;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        if (!$this->hasTran) {
            $this->exec("BEGIN", self::CONN_MASTER);
        }
        $this->hasTran++;
        return true;
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit()
    {
        if ($this->hasTran) {
            $this->hasTran--;
        }
        if (!$this->hasTran) {
            return $this->exec("COMMIT", self::CONN_MASTER);
        }
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        $this->hasTran = 0;
        return $this->exec("ROLLBACK", self::CONN_MASTER);
    }

    /**
     * invokes the read/write connection
     */
    public function insert(array $data, $tableName = null)
    {
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();

        $keys = array_keys($data);
        $sql = "INSERT INTO " . $tableName . "(" . implode(',', $keys) . ")
                VALUES(:" . implode(':, :', $keys) . ":) ";
        foreach ($data as $k => $v) {
            $v = $v === null ? "null" : $this->quote($v);
            $sql = str_replace(":" . $k . ":", $v, $sql);
        }
        self::$_sql[] = $sql;
        return $this->exec($sql, self::CONN_MASTER, true);
    }
    

    /**
     * 根据条件，有则更新，无则插入
     */
    public function insertIf(array $data, array $where, $tableName = null)
    {
        if (!$data || !$where) {
            return false;
        }

        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();

        $whereSql = $this->parseWhere($where);

        $keys = array_keys($data);
        $sql = "INSERT INTO " . $tableName . "(" . implode(',', $keys) . ")
                VALUES(:" . implode(':, :', $keys) . ":)
                ON DUPLICATE KEY UPDATE " . $whereSql;

        foreach ($data as $k => $v) {
            $v = $v === null ? "null" : $this->quote($v);
            $sql = str_replace(":" . $k . ":", $v, $sql);
        }
        self::$_sql[] = $sql;
        return $this->exec($sql, self::CONN_MASTER, true);
    }

    /**
     * invokes the read/write connection
     */
    public function delete($where = array(), $tableName = null)
    {
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();

        $whereStr = $this->parseWhere($where);
        $whereStr = $whereStr ? " WHERE " . $whereStr : "";

        $sql = "DELETE FROM `" . $tableName . "`" . $whereStr;
        self::$_sql[] = $sql;
        $return = $this->exec($sql, self::CONN_MASTER);

        return $return;
    }


    /**
     * Invokes the read/write connection
     */
    public function update(array $data, $where = array(), $tableName = null)
    {
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();

        $whereStr = $this->parseWhere($where);
        $whereStr = $whereStr ? " WHERE " . $whereStr : "";

        $sql = "UPDATE `" . $tableName . "` SET ";
        foreach ($data as $k => $v) {
            $v = $v === null ? "null" : $this->quote($v);
            $sql .= $k . "=" . $v . ",";
        }
        $sql = rtrim($sql, ',');
        $sql .= $whereStr;
        self::$_sql[] = $sql;

        return $this->exec($sql, self::CONN_MASTER);
    }

    /**
     *
     * 自增
     * @param string $field
     * @param array $where
     */
    public function inCrease($field, $where = array(), $number = 1, $tableName = null)
    {
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();

        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";

        $sql = "UPDATE `{$tableName}` SET {$field} = {$field} +{$number} " . $whereSql;
        self::$_sql[] = $sql;
        $this->exec($sql, self::CONN_MASTER);

        return true;
    }


    /**
     *
     * 自减
     * @param string $field
     * @param array $where
     */
    public function deCrement($field, $where = array(), $number = 1, $tableName = null)
    {
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();

        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";

        $sql = "UPDATE `{$tableName}` SET {$field} = {$field} - {$number} " . $whereSql;
        self::$_sql[] = $sql;
        $this->exec($sql, self::CONN_MASTER);
        return true;
    }

    /**
     * sql语句获取数据库数据
     * @param string $sql
     * @param array $data
     */
    public function selectRow($sql, $data = array())
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
        return $this->fetch($sql, self::CONN_SLAVE);
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
            $this->_total = $rsCount['cnt'];
        }
        return $rs;
    }

    public function getTotal()
    {
        return $this->_total;
    }

    //获取单个字段的数据
    public function selectAllByField($sql, $field = "id", $data = array(), $returnCount = false)
    {
        $list = $this->selectAll($sql, $data, $returnCount);
        $rs = array();
        if ($list) {
            foreach ($list as $v) {
                $rs[] = $v[$field];
            }
        }
        return $rs;
    }


    /**
     *
     * 解析where数据
     * @param array $where
     */
    public function parseWhere($where = array())
    {

        if (count($where) < 1) {
            return "";
        }
        $whereSql = "";

        foreach ($where as $k => $v) {
            $param = $k . " = ? ";
            $andOR = "AND";
            $more = 0;
            if (is_array($v)) {
                if (isset($v[0][0]) && $v[0][0] && (is_array($v[0]))) {
                    if ($v) {
                        foreach ($v as $kk => $vv) {
                            $whereSql .= $this->joinSql($vv, $k, $andOR);
                        }
                    }
                    $more = 1;
                } else {
                    list($sign, $vl) = $v;

                    if (isset($v[2]) && $v[2]) {
                        $andOR = $v[2];
                    }
                    if ($vl !== '') {
                        $param = $k . " " . $sign . " ? ";
                        $sign = strtolower($sign);
                        if ($sign == 'in') {
                            $param = $k . " " . $sign . " (?) ";
                        }
                        $v = $vl;
                    } else {
                        $param = $k . " " . $sign . " ";
                    }
                    $andOR = strtoupper($andOR);
                }
            }
            if (!$more) {
                $whereSql .= " $andOR (" . $this->quoteInto($param, $v) . ")";
            }
        }
        $whereSql = trim($whereSql);
        $whereSql = trim($whereSql, "OR");
        $whereSql = trim($whereSql, "AND");
        return $whereSql;
    }

    public function joinSql($v, $feild, $andOR)
    {
        list($sign, $vl) = $v;

        if (isset($v[2]) && $v[2]) {
            $andOR = $v[2];
        }
        if ($vl !== '') {
            $param = $feild . " " . $sign . " ? ";
            $sign = strtolower($sign);
            if ($sign == 'in') {
                $param = $feild . " " . $sign . " (?) ";
            }
            $v = $vl;
        } else {
            $param = $feild . " " . $sign . " ";
        }
        $andOR = strtoupper($andOR);
        $whereSql = " $andOR (" . $this->quoteInto($param, $v) . ")";
        return $whereSql;
    }

    public function quoteInto($param, $v)
    {
        if (is_array($v)) {
            $str = "";
            foreach ($v as $v1) {
                if (!is_array($v1)) {
                    $str .= $v1 === null ? "null" : $this->quote($v1) . ",";
                }
            }
            $str = trim($str, ",");
            return str_replace('?', $str, $param);
        } else {
            return str_replace('?', $v === null ? "null" : $this->quote($v), $param);
        }
    }

    /**
     * 根据条件获取多个
     * @param array $where
     */
    public function gets($where = array(), $orderBy = "", $limit = "", $offset = "", $groupBy = "", $returnCount = false, $tableName = null)
    {
        $whereSql = $this->parseWhere($where);

        $whereSql = $whereSql ? " WHERE " . $whereSql : "";
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();
        $orderBySql = "";
        $groupBySql = "";
        $limitSql = "";
        if ($groupBy) {
            $groupBySql = " GROUP BY " . $groupBy;
        }
        if ($orderBy) {
            $orderBySql = " ORDER BY " . $orderBy;
        }

        if ($offset) {
            $limit = intval($limit);
            $offset = intval($offset);
            $limitSql = " LIMIT {$limit} , {$offset} ";
        }


        $sql = "SELECT *  FROM `{$tableName}` " . $whereSql . $groupBySql . $orderBySql . $limitSql;

        $key = base64_encode($sql) . $returnCount;
        if (isset(self::$sqlinstance[$key]) && self::$sqlinstance[$key]) {
            return self::$sqlinstance[$key];
        }

        $rs = $this->fetchAll($sql, self::CONN_SLAVE);
        if ($returnCount) {
            $sqlCount = "SELECT count(*) as cnt FROM  `{$tableName}` " . $whereSql;
            $rsCount = $this->fetch($sqlCount, self::CONN_SLAVE);
            $this->_total = $rsCount['cnt'];
        }
        self::$sqlinstance[$key] = $rs;
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
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();
        $orderBySql = "";
        $groupBySql = "";
        $limitSql = "";
        if ($groupBy) {
            $groupBySql = " GROUP BY " . $groupBy;
        }
        if ($orderBy) {
            $orderBySql = " ORDER BY " . $orderBy;
        }

        if ($offset) {
            $limit = intval($limit);
            $offset = intval($offset);
            $limitSql = " LIMIT {$limit} , {$offset} ";
        }
        $resultKeyStr = $resultKey ? $resultKey . "," : "";
        $sql = "SELECT " . $resultKeyStr . " " . $field . "  FROM `{$tableName}` " . $whereSql . $groupBySql . $orderBySql . $limitSql;
        $key = base64_encode($sql) . $field . $isMore;
        if (isset(self::$sqlinstance[$key]) && self::$sqlinstance[$key]) {
            return self::$sqlinstance[$key];
        }
        if ($isMore) {
            $rs = $this->fetchAll($sql, self::CONN_SLAVE);
            if (!$rs) {
                return array();
            }
            $result = array();
            foreach ($rs as $key => $value) {
                $result[$value[$resultKey]] = $value[$field];
            }
            self::$sqlinstance[$key] = $result;
            return $result;
        } else {
            $rs = $this->fetch($sql, self::CONN_SLAVE);
            $result = $rs ? $rs[$field] : "";
            self::$sqlinstance[$key] = $result;
            return $result;
        }
    }


    /**
     * 根据条件获取一行
     * @param array $where
     */
    public function get($where, $orderBy = "", $tableName = null)
    {
        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";

        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();

        $sql = "SELECT * FROM `{$tableName}` " . $whereSql;
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }

        $key = base64_encode($sql);
        if (isset(self::$sqlinstance[$key]) && self::$sqlinstance[$key]) {
            return self::$sqlinstance[$key];
        }
        $result = $this->fetch($sql, self::CONN_SLAVE);
        self::$sqlinstance[$key] = $result;
        return $result;
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
        $tableName = $tableName ? $this->prefix . $tableName : $this->getTableName();
        $groupBySql = "";
        if ($groupBy) {
            $groupBySql = " GROUP BY " . $groupBy;
        }
        $sql = "SELECT count(*) as cnt FROM `{$tableName}` " . $whereSql . $groupBySql;
        $return = $this->fetch($sql, self::CONN_SLAVE);

        return $return['cnt'];
    }

    protected function quote($value)
    {
        $data = addslashes($value);
        return "'".$data."'";
    }

    /**
     *
     * 获取sql
     */
    public static function getSql()
    {
        return self::$_sql;
    }

    /**
     *
     * 获取sql
     */
    public static function clearStaticData()
    {
        self::$sqlinstance = [];
        self::$_sql = [];
    }
}