<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31 0031
 * Time: 11:46
 */

namespace Libs\MyRabbitMQ;

class AsyncConsumerMQ extends AsyncBaseMQ{
    private $q_name = 'word'; //队列名
    private $route  = '123456'; //路由key

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
     * @param array|string   $fun_name 回调方法
     * @param bool $auto_ack 自动确认回执
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public function run($fun_name,$auto_ack = true){
        //创建交换机
        $ex = $this->exchange();
        $ex->setType(AMQP_EX_TYPE_DIRECT); //direct类型
        $ex->setFlags(AMQP_DURABLE); //持久化

        //创建队列
        $q = $this->queue();
        $q->setName($this->q_name);// Jo:会自动创建与交换机的绑定关系
        $q->setFlags(AMQP_DURABLE); //持久化
        echo "Message Total:".$q->declareQueue()."\n";

        //绑定交换机与队列，并指定路由键
        echo 'Queue Bind: '.$q->bind($this->exchange, $this->route)."\n";

        //阻塞模式接收消息
        echo "Message:\n";
        while(true){
            if($q->declareQueue() <=0 ) break;

            if ($auto_ack) $q->consume($fun_name, AMQP_AUTOACK);
            else $q->consume($fun_name);
        }
        $this->close();
    }
}
