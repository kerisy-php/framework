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

class MysqlAsync extends CoroutinePool
{
    protected $config = null;
    protected static $maxCount = [];

    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    protected function execute($data)
    {
        $sql = $data['sql'];
        $connType = $data['connType'];
        $func = $data['func'];

        if (!isset(self::$pool[$connType]) || !self::$pool[$connType]) {//代表目前没有可用的连接
            $this->prepareOne($data);
            self::$commands[$connType][]=$data;
            return ;
        } else {
            $client = array_pop(self::$pool[$connType]);
        }

        $client->query($sql, function ($client, $result) use ($data, $func, $connType) {
            try {
                if ($result === false) {
                    if ($client->errno == 2006 || $client->errno == 2013) {//断线重连
                        $this->reconnect($data, $client);
                        unset($client);
                        array_unshift(self::$commands[$connType],$data);
                    } else {
                        throw new \Exception("[mysql_client Error]:" . $client->error . "[sql]:" . $data['sql']);
                    }
                } else {
                    if($func == 'lastInsertId'){
                        $data['result'] = $client->insert_id;
                    }elseif($func == 'fetch'){
                        $data['result'] = $result?current($result):null;
                    }
                    else{
                        $data['result'] = $result;
                    }
//                    $data['result']['client_id'] = $client->client_id;
//                    $data['result']['result'] = $result;
//                    $data['result']['affected_rows'] = $client->affected_rows;
//                    $data['result']['insert_id'] = $client->insert_id;
                    unset($data['sql']);
                    //不是绑定的连接就回归连接
                    $this->pushToPool($client,$connType);
                    //给worker发消息
                    call_user_func([$this, 'distribute'], $data);
                }
            } catch (\Exception $e) {
                $data['result']['exception'] = $e->getMessage();
                call_user_func([$this, 'distribute'], $data);
            }
        });
    }

    protected function reconnect($data, $tmpClient = null)
    {

        if ($tmpClient == null) {
            $client = new \swoole_mysql();
        } else {
            $client = $tmpClient;
        }

        $connType = $data['connType'];

        $serverConfig = isset($this->config[$connType])?$this->config[$connType]:$this->config['master'];

        $set = array(
            'host' => $serverConfig['host'],
            'user' => $serverConfig['user'],
            "port" => $serverConfig['port'],
            'password' => $serverConfig['password'],
            'database' => $serverConfig['db_name'],
        );

        $nowConnectNo = self::$maxCount;
        $client->connect($set, function ($client, $result) use ($tmpClient, $nowConnectNo, $data) {
            try {
                $connType = $data['connType'];
                if (!$result) {
                    throw new \Exception("[mysql connect fail]" . $client->connect_error);
                } else {
                    $client->isAffair = false;
                    $client->client_id = $tmpClient ? $tmpClient->client_id : $nowConnectNo;
                    $this->pushToPool($client,$connType);
                }
            } catch (\Exception $e) {
                $data['result']['exception'] = $e->getMessage();
                call_user_func([$this, 'distribute'], $data);
            }
        });
    }

    /**
     * 准备一个mysql
     */
    public function prepareOne($data)
    {
        $connType = $data['connType'];
        if(isset(self::$prepareLock[$connType]) && self::$prepareLock[$connType]) return ;
        
        $check = isset(self::$maxCount[$connType])?self::$maxCount[$connType]:0;
        if ($check >= $this->config['asyn_max_count']) {
            return;
        }

        self::$maxCount[$connType] = $check+1;
        $this->reconnect($data);
    }
}