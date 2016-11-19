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

namespace Kerisy\Coroutine\Db;


use Kerisy\Coroutine\Base\CoroutineBase;
use Kerisy\Coroutine\Base\CoroutineResult;

class DbCoroutine implements CoroutineBase
{
    /**
     * @var $pdoPool
     */
    public $pdoPool;
    /**
     * @var data => ['sql'=>'','connType'=>'','func'=>''];
     */
    public $data;
    public $result;

    public function __construct($pdoPool)
    {
        $this->result = CoroutineResult::getInstance();
        $this->pdoPool = $pdoPool;
    }


    public function set($sql,$connType,$func){
        $this->data['sql'] = $sql;
        $this->data['connType'] = $connType;
        $this->data['func'] = $func;
        yield $this;
    }


    public function send(callable $callback)
    {
        $this->pdoPool->perform($callback, $this->data);
    }

    public function getResult()
    {
        return $this->result;
    }
}