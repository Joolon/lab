<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31 0031
 * Time: 11:46
 */

namespace Libs\MyRabbitMQ;

class AsyncProduceMQ extends AsyncBaseMQ {

    /**
     * ProduceMQ constructor.
     * @param $conf
     * @param $exchange
     * @throws \AMQPConnectionException
     */
    public function __construct($conf,$exchange){
        parent::__construct($conf,$exchange);
    }

    /** 只控制发送成功 不接受消费者是否收到
     * @param $message
     * @param $k_route
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function send($message,$k_route = null){
        $channel = $this->channel();//创建频道实例
        $this->exchange();//创建交换机对象实例

        //消息内容
        $channel->startTransaction();//开始事务
        $sendEd = true;

        //$this->type_fanout($message);
        $this->type_direct($message,$k_route);

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
    public function type_fanout($message) {
        $sendEd  = $this->AMQPExchange->publish($message);// 交换机名称、
        echo "Send Message:".$sendEd."\n";
    }

    /**
     * direct：将Exchange接收到的消息发送给Binding key与Routing key完全匹配的Queue
     * @param $message
     * @param $k_route
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function type_direct($message,$k_route){
        // 发送消息到指定的交换机、指定Route规则的队列中
        // Binding key 用户消息中携带过去
        if(is_array($k_route)){
            foreach($k_route as $route){
                $sendEd  = $this->AMQPExchange->publish($message,$route);
                echo "Send Message:".$route.'  --->>>  '.$sendEd."\n";
            }
        }else{
            $sendEd  = $this->AMQPExchange->publish($message,$k_route);
            echo "Send Message:".$k_route.'  --->>>  '.$sendEd."\n";
        }
    }
}
