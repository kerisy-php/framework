<?php
/**
 * User: Peter Wang
 * Date: 17/3/21
 * Time: 上午10:53
 */

namespace Kerisy\Foundation\Storage;


class Model extends Pdo
{
    private $alias = [];  //记录全局的语句参数
    
    //where语句
    public function where($key, $compute, $value=null, $join = "AND")
    {
        $compute = strtolower($compute);

        if($value === null){
            $tmp = [$compute, null, $join];
        }else{
            if(in_array($compute, ["=","!=",">",">=","<","<=","in"])){
                $tmp = [$compute, $value, $join];
            }
        }

        if(isset($this->alias['where'][$key])){
            array_push($this->alias['where'][$key], $tmp);
        }else {
            $this->alias['where'][$key] = [$tmp];
        }
//        var_dump($this->alias['where']);
        return $this;
    }

    public function eqWhere($key, $value)
    {
        $this->where($key, "=", $value);
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
    public function order($order)
    {
        $this->alias['order'] = $order;
        return $this;
    }

    //group语句
    public function group($group)
    {
        $this->alias['group'] = $group;
        return $this;
    }

    //查询语句
    public function select()
    {
        $data = $this->formatData();
        if($data){
            foreach ($data as $k=>$v){
                $this->where($k, $v);
            }
        }

        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $order = isset($this->alias['order'])?$this->alias['order']:'';
        $group = isset($this->alias['group'])?$this->alias['group']:'';
        $limit = isset($this->alias['limit'])?$this->alias['limit']:'';
        $offset = isset($this->alias['offset'])?$this->alias['offset']:'';

        return $this->gets($where, $order, $limit, $offset, $group);
    }

    //查询一条
    public function find()
    {
        $data = $this->formatData();
        if($data){
            foreach ($data as $k=>$v){
                $this->where($k, $v);
            }
        }

        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $order = isset($this->alias['order'])?$this->alias['order']:'';
        $group = isset($this->alias['group'])?$this->alias['group']:'';

        return $this->get($where, $order,$group);
    }


    private function collectData()
    {
        $className = get_called_class();
        $reflect = new \ReflectionClass($className);
        $fields = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
        $fieldData = [];
        if ($fields) {
            foreach ($fields as $field) {
                $name = $field->name;
                $tmpName = strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $name));
                $class = $field->class;
                if ($class == $className) {
                    $fieldData[$tmpName] = $this->$name;
                }
            }
        }
        return $fieldData;
    }

    private function formatData()
    {
        $fieldData = $this->collectData();
        if (!$fieldData) return [];
        $formatData = [];
        foreach ($fieldData as $k => $v) {
            if ($v !== null) {
                $formatData[$k] = $v;
            }
        }

        return $formatData;
    }


    public function create()
    {
        $data = $this->formatData();
        return $this->insert($data);
    }


    public function save()
    {
        $data = $this->formatData();
        if(isset($data['id'])){
            $where = isset($data['id'])?["id"=>$data['id']]:[];
        }else{
            $where = $data;
        }

        return $this->insertIf($data, $where);
    }


    public function remove()
    {
        $data = $this->formatData();
        if($data){
            foreach ($data as $k=>$v){
                $this->where($k, $v);
            }
        }
        $where = isset($this->alias['where'])?$this->alias['where']:[];
        return $this->delete($where);
    }
}