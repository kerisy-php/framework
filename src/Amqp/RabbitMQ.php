<?php
/**
 *  RabbitMq 类
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/6/8
 */
namespace Kerisy\Amqp;
use Kerisy\Core\Config;
use Kerisy\Core\Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMq{
    protected $config = null;
    protected $channel = null;
    protected $connection = null;

    const EXCHANGE_TYPE_DIRECT = 'direct';

    const EXCHANGE_TYPE_FANOUT = 'fanout';

    const EXCHANGE_TYPE_TOPIC = 'topic';

    const EXCHANGE_TYPE_HEADER = 'header';

    const MESSAGE_DURABLE_YES = 2;

    const MESSAGE_DURABLE_NO = 1;

    const CONNECT_TIMEOUT=1.0; //链接超时时间

    const READ_TIMEOUT=3.0;//读取超时时间


    function __construct($configKey = "default")
    {
        $configObj = new Config("mq");
        $config = $configObj->get($configKey);
        try{
            $this->connection = new AMQPStreamConnection($config['host'], $config['port'],
                $config['user'], $config['password'], $config['vhost'],false,'AMQPLAIN',null,'en_US',self::CONNECT_TIMEOUT,self::READ_TIMEOUT);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
        $this->channel = $this->connection->channel();
        //脚本退出前，关闭连接
        register_shutdown_function([$this,'close']);
    }

    /**
     * @param $message
     * @param $queue
     * @param $exchange
     * @param string $type
     * @param bool $passive 队列已存在是否新建队列
     * @param bool $durable  是否持久化保存
     * @param bool $exclusive 是否是排他队列,排他队列首次可见
     * @param bool $auto_delete 队列如果没有消息,是否自动删除
     * @return bool
     */
    function publishMessage($message,$queue,$exchange,$type=self::EXCHANGE_TYPE_DIRECT,$passive=false,$durable=true,$exclusive=false,$auto_delete=false){
        $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $auto_delete);
        $this->channel->exchange_declare($exchange, $type, $passive, $durable, $auto_delete);
        $this->channel->queue_bind($queue, $exchange);
        $delivery_mode = ($durable)?self::MESSAGE_DURABLE_YES:self::MESSAGE_DURABLE_NO;
        $message = new AMQPMessage($message, array('content_type' => 'text/plain', 'delivery_mode' => $delivery_mode));
        $this->channel->basic_publish($message, $exchange);
        return true;
    }

    /**
     * @param $message
     * @param $queue
     * @param $exchange
     * @param string $type
     * @param bool $passive 队列已存在是否新建队列
     * @param bool $durable  是否持久化保存
     * @param bool $exclusive 是否是排他队列,排他队列首次可见
     * @param bool $auto_delete 队列如果没有消息,是否自动删除
     * @return bool
     */
    function getMessage($queue,$exchange,$type=self::EXCHANGE_TYPE_DIRECT,$passive=false,$durable=true,$exclusive=false,$auto_delete=false){
        $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $auto_delete);
        $this->channel->exchange_declare($exchange, $type, $passive, $durable, $auto_delete);
        $this->channel->queue_bind($queue, $exchange);
        $message = $this->channel->basic_get($queue);
        return $message;
    }

    function getBody($message){
        if($message){
            return $message->body;
        }else{
            return null;
        }
    }

    function ack($message){
        if(!$message) return true;
        $this->channel->basic_ack($message->delivery_info['delivery_tag']);
        return true;
    }

    /**
     * @param $message
     * @param $queue
     * @param $exchange
     * @param string $type
     * @param bool $passive 队列已存在是否新建队列
     * @param bool $durable  是否持久化保存
     * @param bool $exclusive 是否是排他队列,排他队列首次可见
     * @param bool $auto_delete 队列如果没有消息,是否自动删除
     * @return bool
     */
    function publishMessageWhithConfig($message,$queue,$exchange,$ackfn,$nackfn,$returnfn,$type=self::EXCHANGE_TYPE_DIRECT,$passive=false,$durable=true,$exclusive=false,$auto_delete=false){
        $this->channel->set_ack_handler($ackfn);
        $this->channel->set_nack_handler($nackfn);
        $this->channel->set_return_listener($returnfn);
//        $this->channel->set_ack_handler(
//            function (AMQPMessage $message) {
//                echo "Message acked with content " . $message->body . PHP_EOL;
//            }
//        );
//
//        $this->channel->set_nack_handler(
//            function (AMQPMessage $message) {
//                echo "Message nacked with content " . $message->body . PHP_EOL;
//            }
//        );
//
//        $this->channel->set_return_listener(
//            function ($replyCode, $replyText, $exchange, $routingKey, AMQPMessage $message) {
//                echo "replyCode:".$replyCode."\r\n";
//                echo "replyText:".$replyText."\r\n";
//                echo "exchange:".$exchange."\r\n";
//                echo "routingKey:".$routingKey."\r\n";
//                echo "Message returned with content " . $message->body . PHP_EOL;
//            }
//        );

        $this->channel->confirm_select();
        $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $auto_delete);
        $this->channel->exchange_declare($exchange, $type, $passive, $durable, $auto_delete);

        $this->channel->queue_bind($queue, $exchange);

        $message = new AMQPMessage($message, array('content_type' => 'text/plain'));
        $this->channel->basic_publish($message, $exchange, null, true);

        $this->channel->wait_for_pending_acks_returns();

        return true;
    }

    public function close(){
        if($this->connection){
            $this->connection->close();
        }

        if($this->channel){
            $this->channel->close();
        }

    }


}

