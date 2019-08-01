<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31 0031
 * Time: 11:46
 */

namespace Libs\MyRabbitMQ;

class ProduceMQ extends BaseMQ {
    private $routes = ['111111','222222']; //路由key

    /**
     * ProductMQ constructor.
     * @throws \AMQPConnectionException
     */
    public function __construct(){
        parent::__construct();
    }

    /** 只控制发送成功 不接受消费者是否收到
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function run(){
        $channel = $this->channel();//创建频道
        $this->exchange();//创建交换机对象

        //消息内容
        $channel->startTransaction();//开始事务
        $sendEd = true;

        $message = 'product message '.rand(10000, 99999);

        //$this->example_fanout($message);
        //$this->example_direct($message);


//        print_r($this->routes);exit;
//        for($i = 0;$i < 10;$i ++){
//            $message = 'product message '.rand(10000, 99999);
//            foreach($this->routes as $route){
//                $sendEd  = $this->AMQPExchange->publish($message);// 交换机名称、
//                echo "Send Message:".$route.'  --->>>  '.$sendEd."\n";
//            }
//        }

        if(!$sendEd){
            $channel->rollbackTransaction();// 回滚事务
        }
        $channel->commitTransaction(); //提交事务
        $this->close();
        die;
    }

    /**
     * fanout：将Exchange接收到的消息发送给与其绑定的所有Quenue
     * @param $message
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function example_fanout($message) {
        $sendEd  = $this->AMQPExchange->publish($message);// 交换机名称、
        echo "Send Message:".$sendEd."\n";
    }

    /**
     * direct：将Exchange接收到的消息发送给Binding key与Routing key完全匹配的Queue
     * @param $message
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function example_direct($message){
        foreach($this->routes as $route){
            // 发送消息到指定的交换机、指定Route规则的队列中
            // Binding key 用户消息中携带过去
            $sendEd  = $this->AMQPExchange->publish($message,$route);
            echo "Send Message:".$route.'  --->>>  '.$sendEd."\n";
        }
    }
}
