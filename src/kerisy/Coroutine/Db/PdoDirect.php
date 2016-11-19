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


use Kerisy\Coroutine\Base\CoroutinePool;
use Kerisy\Support\Log;

class PdoDirect extends CoroutinePool
{
    protected $client = null;

    public function __construct($client)
    {
        parent::__construct();
        $this->client = $client;
    }


    protected function execute($data)
    {
        $sql = $data['sql'];
        $connType = $data['connType'];
        $method = $data['func'];

        if (!$method || $method == 'lastInsertId') {
            $this->client[$connType]->exec($sql);
            if ($method) {
                $result = $this->client[$connType]->$method();
            }
            if ($this->client[$connType]->errorCode() != '00000') {
                $error = $this->client[$connType]->errorInfo();
                $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                $data['result']['exception'] = $errorMsg;
                //发消息
                call_user_func([$this, 'distribute'], $data);
                throw new \Exception($errorMsg);
            }
            $data['result'] = $result;

        } else {
            $query = $this->client[$connType]->query($sql);
            $result = $query->$method();
            if ($this->client[$connType]->errorCode() != '00000') {
                $error = $this->client[$connType]->errorInfo();
                $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                $data['result']['exception'] = $errorMsg;
                //发消息
                call_user_func([$this, 'distribute'], $data);
                throw new \Exception($errorMsg);
            }

            $data['result'] = $result;
        }
        //发消息
        call_user_func([$this, 'distribute'], $data);
    }


}