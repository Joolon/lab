<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31 0031
 * Time: 11:46
 */

namespace Libs\MyRabbitMQ;

class AsyncConsumerMQ extends AsyncBaseMQ{

    /**
     * AsyncConsumerMQ constructor.
     * @param $conf
     * @param $exchange
     * @throws \AMQPConnectionException
     */
    public function __construct($conf,$exchange){
        parent::__construct($conf,$exchange);
    }

    /**
     * 接受消息 如果终止 重连时会有消息
     * @param array|string $fun_name      回调方法
     * @param bool         $queue_name    队列名称
     * @param bool         $binding_route 路由规则
     * @param bool         $auto_ack      自动确认回执
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public function run($fun_name,$queue_name,$binding_route = null,$auto_ack = true){
        $this->exchange();//创建交换机实例
        $this->AMQPExchange->setType(AMQP_EX_TYPE_DIRECT); //direct类型
        $this->AMQPExchange->setFlags(AMQP_DURABLE); //持久化

        $this->queue();//创建队列实例
        $this->AMQPQueue->setName($queue_name);// Jo:会自动创建与交换机的绑定关系
        $this->AMQPQueue->setFlags(AMQP_DURABLE); //持久化
        echo "Message Total:".$this->AMQPQueue->declareQueue()."\n";

        if($binding_route){
            // 根据路由规则绑定交换机与队列，并指定路由键
            echo 'Queue Bind: '.$this->AMQPQueue->bind($this->exchange, $binding_route)."\n";
        }

        //阻塞模式接收消息
        echo "Message:\n";
        while(true){
            sleep(1);
            if($this->AMQPQueue->declareQueue() <=0 ) break;

            if ($auto_ack) $this->AMQPQueue->consume($fun_name, AMQP_AUTOACK);
            else $this->AMQPQueue->consume($fun_name);

        }
        $this->close();
    }
}
