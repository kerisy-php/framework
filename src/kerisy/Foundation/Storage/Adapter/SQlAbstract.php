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

use Kerisy\Foundation\Shortcut;
use Kerisy\Support\Log;

abstract class SQlAbstract
{
    use Shortcut;

    protected $alias = [];  //记录全局的语句参数
    protected $hasTran = 0;//是否有事务
    protected $_total = 0;
    protected static $_sql = array();
    protected static $sqlinstance = array();
    protected $tableName = null;
    protected static $prefix = "";
    protected $field = null;

    const CONN_MASTER = "master";
    const CONN_SLAVE = "slave";

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
        $table = self::$prefix.$table;
        return $this->tableName = $table;
    }

    public function getPrefix()
    {
        return self::$prefix;
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
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();

        $keys = array_keys($data);
        $sql = "INSERT INTO " . $tableName . "(" . implode(',', $keys) . ")
                VALUES(:" . implode(':, :', $keys) . ":) ";
        foreach ($data as $k => $v) {
            $v = $v === null ? "null" : $this->quote($v);
            $sql = str_replace(":" . $k . ":", $v, $sql);
        }

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

        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();

        $whereSql = $this->parseWhere($where);

        $keys = array_keys($data);
        $sql = "INSERT INTO " . $tableName . "(" . implode(',', $keys) . ")
                VALUES(:" . implode(':, :', $keys) . ":)
                ON DUPLICATE KEY UPDATE " . $whereSql;

        foreach ($data as $k => $v) {
            $v = $v === null ? "null" : $this->quote($v);
            $sql = str_replace(":" . $k . ":", $v, $sql);
        }

        return $this->exec($sql, self::CONN_MASTER, true);
    }

    /**
     * invokes the read/write connection
     */
    public function delete($where = array(), $tableName = null)
    {
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();

        $whereStr = $this->parseWhere($where);
        $whereStr = $whereStr ? " WHERE " . $whereStr : "";

        $sql = "DELETE FROM `" . $tableName . "`" . $whereStr;

        $return = $this->exec($sql, self::CONN_MASTER);

        return $return;
    }


    /**
     * Invokes the read/write connection
     */
    public function update(array $data, $where = array(), $tableName = null)
    {
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();

        $whereStr = $this->parseWhere($where);
        $whereStr = $whereStr ? " WHERE " . $whereStr : "";

        $sql = "UPDATE `" . $tableName . "` SET ";
        foreach ($data as $k => $v) {
            $v = $v === null ? "null" : $this->quote($v);
            $sql .= $k . "=" . $v . ",";
        }
        $sql = rtrim($sql, ',');
        $sql .= $whereStr;


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
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();

        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";

        $sql = "UPDATE `{$tableName}` SET {$field} = {$field} +{$number} " . $whereSql;

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
        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();

        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";

        $sql = "UPDATE `{$tableName}` SET {$field} = {$field} - {$number} " . $whereSql;

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
     * 解析where数据
     * @param array $where
     * @return string
     */
    public function parseWhere($where = array()){
        $whereSql = $this->_parseWhere($where);
        $whereSql = trim($whereSql);
        $whereSql = trim($whereSql, "OR");
        $whereSql = trim($whereSql, "AND");
        return $whereSql;
    }

    //where语句
    public function where($key, $compute, $value=null, $join = "AND")
    {
        $compute = strtolower($compute);

        if($value === null){
            $tmp = [$compute, null, $join];
        }else{
            if(in_array($compute, ["=","!=",">",">=","<","<=","in"])){
                $tmp = [$compute, $value, $join];
            }else{
                return $this;
            }
        }

        if(isset($this->alias['where'][$key])){
            array_push($this->alias['where'][$key], $tmp);
        }else {
            $this->alias['where'][$key] = [$tmp];
        }
        return $this;
    }

    public function table($table)
    {
        $this->alias['table'] = $table;
        return $this;
    }

    public function isNull($key)
    {
        $this->where($key, "IS NULL");
        return $this;
    }

    public function isNotNull($key)
    {
        $this->where($key, "IS NOT NULL");
        return $this;
    }

    public function whereNeq($key, $value)
    {
        $this->where($key, "!=", $value);
        return $this;
    }

    public function whereEq($key, $value)
    {
        $this->where($key, "=", $value);
        return $this;
    }

    public function whereIn($key, $value)
    {
        $this->where($key, "in", $value);
        return $this;
    }

    //limit语句
    public function limit($limit)
    {
        $this->alias['limit'] = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->alias['offset'] = $offset;
        return $this;
    }

    //order语句
    public function orderBy($order)
    {
        $this->alias['order'] = $order;
        return $this;
    }

    //group语句
    public function groupBy($group)
    {
        $this->alias['group'] = $group;
        return $this;
    }


    /**
     * 查询多条
     * @return mixed
     */
    public function find()
    {
        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $order = isset($this->alias['order'])?$this->alias['order']:'';
        $group = isset($this->alias['group'])?$this->alias['group']:'';
        $limit = isset($this->alias['limit'])?$this->alias['limit']:'';
        $offset = isset($this->alias['offset'])?$this->alias['offset']:'';
        $table = isset($this->alias['table'])?$this->alias['table']:'';

        return $this->gets($where, $order, $limit, $offset, $group, false, $table);
    }

    //查询一条
    public function findOne()
    {
        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $order = isset($this->alias['order'])?$this->alias['order']:'';
        $group = isset($this->alias['group'])?$this->alias['group']:'';
        $table = isset($this->alias['table'])?$this->alias['table']:'';

        return $this->get($where, $order,$group, $table);
    }

    /**
     * 删除
     * @return mixed
     */
    public function remove()
    {
        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $table = isset($this->alias['table'])?$this->alias['table']:'';

        return $this->delete($where, $table);
    }

    /**
     *
     * 解析where数据
     * @param array $where
     * @param string $andOR
     */
    private function _parseWhere($where = array(), $andOR='AND')
    {
        if (count($where) < 1) {
            return "";
        }
        $whereSql = "";

        foreach ($where as $k => $v) {
            $param = $k . " = ? ";
            $more = 0;
            if (is_array($v)) {
                if (isset($v[0][0]) && $v[0][0] && (is_array($v[0]))) {
                    if ($v) {
                        foreach ($v as $kk => $vv) {
                            $tmpWhere = [];
                            $tmpWhere[$k] = $vv;
                            $whereSql .= $this->_parseWhere($tmpWhere, $andOR);
                        }
                    }
                    $more = 1;
                } else {

                    list($sign) = $v;

                    if (isset($v[2]) && $v[2]) {
                        $andOR = $v[2];
                    }

                    if (isset($v[1]) && $v[1] !== null) {
                        $param = $k . " " . $sign . " ? ";
                        $sign = strtolower($sign);
                        if ($sign == 'in') {
                            $param = $k . " " . $sign . " (?) ";
                        }
                        $v = $v[1];
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
            $this->_total = $rsCount['cnt'];
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
            $rs = $this->fetch($sql, self::CONN_SLAVE);
            $result = $rs ? $rs[$field] : "";
            return $result;
        }
    }


    /**
     * 根据条件获取一行
     * @param array $where
     */
    public function get($where, $orderBy = "", $groupBy="", $tableName = null)
    {

        $whereSql = $this->parseWhere($where);
        $whereSql = $whereSql ? " WHERE " . $whereSql : "";

        $tableName = $tableName ? self::$prefix . $tableName : $this->getTableName();

        $field = $this->field?$this->field:"*";

        $sql = "SELECT {$field} FROM `{$tableName}` " . $whereSql;
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        if ($groupBy) {
            $sql .= " GROUP BY " . $orderBy;
        }
        $this->field = null;

        return $this->fetch($sql, self::CONN_SLAVE);
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
        return $return['cnt'];
    }


    public function field($fieldStr){
        $this->field = $fieldStr;
        return $this;
    }


    protected function quote($value)
    {
        if(!is_string($value)) return $value;
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
        self::$_sql = [];
    }

    /**
     *  导入
     *
     * @param $sqlPath
     * @param string $old_prefix
     * @param string $new_prefix
     * @param string $exceptTable 排除的table
     * @return bool
     */
    function import($sqlPath,$old_prefix="",$new_prefix="", $exceptTable=[]){
        if(is_file($sqlPath)){
            $txt = file_get_contents($sqlPath);
            if(!$txt) return true;
            $sqlArr = $this->clearSql($txt,$old_prefix,$new_prefix);
//            dump($sqlArr);
            if($sqlArr){
                foreach ($sqlArr as $sv){
                    $isExcept = 0;
                    if($exceptTable){
                        foreach ($exceptTable as $ev){
                            if(preg_match_all("/`{$ev}`/i", $sv, $match)){
                                $this->dump($match);
                                $isExcept = 1;
                                continue;
                            }
                        }
                    }
                    if(!$isExcept){
                        Log::show($sv);
                        $this->exec($sv);
                    }

                }
            }
        }
        return true;
    }

    /*
		参数：
		$old_prefix:原表前缀；
		$new_prefix:新表前缀；
		$separator:分隔符 参数可为";\n"或";\r\n"或";\r"
	*/
    function clearSql($content,$old_prefix="",$new_prefix="",$separator=";\n")
    {
        $commenter = array('#','--','\/\*');
        $content = str_replace(array($old_prefix, "\r"), array($new_prefix, "\n"), $content);//替换前缀
        //通过sql语法的语句分割符进行分割
        $segment = explode($separator,trim($content));
        //去掉注释和多余的空行
        $data=array();
        foreach($segment as  $statement)
        {
            $sentence = explode("\n",$statement);
            $newStatement = array();
            foreach($sentence as $subSentence)
            {
                if('' != trim($subSentence))
                {
                    //判断是会否是注释
                    $isComment = false;
                    foreach($commenter as $comer)
                    {
                        if(preg_match("/^(".$comer.")/is",trim($subSentence)))
                        {
                            $isComment = true;
                            break;
                        }
                    }
                    //如果不是注释，则认为是sql语句
                    if(!$isComment)
                        $newStatement[] = $subSentence;
                }
            }
            $data[] = $newStatement;
        }
        //组合sql语句
        foreach($data as  $statement)
        {
            $newStmt = '';
            foreach($statement as $sentence)
            {
                $newStmt = $newStmt.trim($sentence)."\n";
            }
            if(!empty($newStmt))
            {
                $result[] = $newStmt;
            }
        }
        return $result;
    }
}