<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/30
 */
namespace Kerisy\Kfk;

use Kerisy\Core\Config;

class Kfk{

    const TIME_OUT = 1000;

    private  $rk = null;
    private  $topic = null;
    private  $type=0;//0-Producer,1-KafkaConsumer
    

    /**
     * 生产初始化
     * @param string $configKey
     * @throws Exception
     */
    function getProductInstance($configKey="default")
    {
        $configObj = new Config("kfk");
        $config = $configObj->get($configKey);
        try{
            $rk = new \RdKafka\Producer();
            $rk->setLogLevel(LOG_DEBUG);
            $rk->addBrokers($config);
            $this->rk = $rk;

            $this->type = 0;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 消费初始化
     * @param $groupName
     * @param string $configKey
     * @throws Exception
     */
    function getCustomerInstance($groupName,$configKey="default"){
        $configObj = new Config("kfk");
        $config = $configObj->get($configKey);
        try{
            $conf = new \RdKafka\Conf();

            $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
                switch ($err) {
                    case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
//                        echo "Assign: ";
//                        var_dump($partitions);
                        $kafka->assign($partitions);
                        break;

                    case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
//                        echo "Revoke: ";
//                        var_dump($partitions);
                        $kafka->assign(NULL);
                        break;

                    default:
                        throw new \Exception($err);
                }
            });

            $conf->set('group.id', $groupName);

            $conf->set('metadata.broker.list', $config);

            $topicConf = new \RdKafka\TopicConf();

            $topicConf->set('auto.offset.reset', 'smallest');

            $conf->setDefaultTopicConf($topicConf);

            $rk = new \RdKafka\KafkaConsumer($conf);
            
            $this->type = 1;
            $this->rk = $rk;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $topicName
     * @param string $offsetMethod, value= none, file, broker
     * @return bool
     */
    function setTopic($topicName){
        if($this->type==0){
            $topic = $this->rk->newTopic($topicName);
            $this->topic = $topic;
        }else{
            $this->rk->subscribe([$topicName]);
        }
        return true;
    }

    /**
     * 发送消息
     * @return mixed
     */
    function send($msg,$key=null,$partition=RD_KAFKA_PARTITION_UA){
        return $this->topic->produce($partition,0,$msg,$key);
    }

    /**
     * 获取消息
     * @return mixed
     * @throws Exception
     */
    function get($number=1){
        $result = [];
        for($i=0;$i<$number;$i++){
            echo $i."\r\n";
            $msg = $this->rk->consume(self::TIME_OUT);
//            print_r($msg);
            $rs = $this->_parseData($msg);
            if($rs){
                $result[] = $rs;
            }else{
                break;
            }
        }
        return $result;
    }

    /**
     * 分析数据
     * @param $msg
     * @return array
     */
    function _parseData($msg)
    {
        $result = [];
        if (!$msg) {
            return $result;
        }
        switch ($msg->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                if ($msg->key) {
                    $result = [$msg->key => $msg->payload];
                } else {
                    $result = $msg->payload;
                }
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                break;
            default:
                break;
        }
        return $result;
    }

}