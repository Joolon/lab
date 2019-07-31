<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31 0031
 * Time: 11:46
 */

namespace Libs\MyRabbitMQ;

class ConsumerMQ extends BaseMQ{
    private $q_name = 'hello'; //队列名
    private $route  = 'hello'; //路由key

    /**
     * ConsumerMQ constructor.
     * @throws \AMQPConnectionException
     */
    public function __construct(){
        parent::__construct();
    }

    /** 接受消息 如果终止 重连时会有消息
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public function run(){

        //创建交换机
        $ex = $this->exchange();
        $ex->setType(AMQP_EX_TYPE_DIRECT); //direct类型
        $ex->setFlags(AMQP_DURABLE); //持久化
        //echo "Exchange Status:".$ex->declare()."\n";

        //创建队列
        $q = $this->queue();
        //var_dump($q->declare());exit();
        $q->setName($this->q_name);
        $q->setFlags(AMQP_DURABLE); //持久化
        //echo "Message Total:".$q->declareQueue()."\n";

        //绑定交换机与队列，并指定路由键
        echo 'Queue Bind: '.$q->bind($this->exchange, $this->route)."\n";

        //阻塞模式接收消息
        echo "Message:\n";
        while(true){
            $q->consume(function($envelope, $queue){
                $msg = $envelope->getBody();
                echo $msg."\n"; //处理消息
                $queue->ack($envelope->getDeliveryTag()); //手动发送ACK应答
            });
            //$q->consume('processMessage', AMQP_AUTOACK); //自动ACK应答
        }
        $this->close();
    }
}