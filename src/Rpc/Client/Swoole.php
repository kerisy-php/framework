<?php
/**
 *  Swoole RPC client
 *  
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/5/31
 */
namespace Kerisy\Rpc\Client;

use Kerisy\Core\Exception;

class Swoole extends Base{
 
    private $host=null;
    private $port=null;
    private $client = null;
    private $servicePath = null;
    private $params = null;
    private $sockType = null;
    static $timeout = 2;
    
    function __construct($host,$port,$isAsync=0)
    {
        if($isAsync ==1){
            $sockType = SWOOLE_SOCK_ASYNC;
        }else{
            $sockType = SWOOLE_SOCK_SYNC;
        }
        $client = new \swoole_client(SWOOLE_SOCK_TCP,$sockType);
        $this->host = $host;
        $this->port =$port;
        $this->client = $client;
        $this->sockType =$sockType;
    }

    /**
     * request method
     * @param $servicePath service route path
     * @param mixed $params
     */
    function invoke($servicePath,$params=array()){
        $this->params = $params;
        $this->servicePath = $servicePath;

     
        if ($this->sockType == SWOOLE_SOCK_ASYNC) {
            $this->client->on('connect', [$this, 'onClientConnect']);
            $this->client->on('receive', [$this, 'onClientReceive']);
            $this->client->on('error', [$this, 'onClientError']);
            $this->client->on('close', [$this, 'onClientClose']);
            if (!$this->client->connect($this->host, $this->port, self::$timeout)) {
                throw new \Exception(socket_strerror($this->client->errCode));
            }
        } else {
            //同步处理
            $data = $this->syncSendAndReceive($this->client);
            return $data;
        }
    }

    function syncSendAndReceive($client){
        if(!$client->isConnected()){
            if (!$client->connect($this->host, $this->port, self::$timeout)) {
                throw new \Exception("connect failed");
            }
        }
        if($this->send($client,$this->servicePath,$this->params)){
            try {
                $data = $client->recv();
            }catch (Exception $e){
                throw new \Exception(socket_strerror($client->errCode));
            }
            if ($data === false) {
                throw new \Exception(socket_strerror($client->errCode));
            }
            $data = $this->getResult($data);
            return $data;
        }else {
            throw new \Exception(socket_strerror($client->errCode));
        }
        $data = $this->getResult("");
        return $data;
    }
    
    public function onClientConnect($client){
        $this->send($client,$this->servicePath,$this->params);
    }

    public function onClientReceive($client,$dataBuffer){
        if ($dataBuffer === "") {
            throw new \Exception("connection closed");
        }
        else{
            if ($dataBuffer === false) {
                throw new \Exception(socket_strerror($client->errCode));
            }else{
                $data = $this->getResult($dataBuffer);
            }
        }
        if(gettype($this->params) == "object"){
            call_user_func_array($this->params,array($data));
        }
    }

    public function onClientError($client){
        throw new \Exception(socket_strerror($client->errCode));
    }

    public function onClientClose($client){

    }

}