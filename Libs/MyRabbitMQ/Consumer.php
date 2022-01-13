<?php

namespace Libs\MyRabbitMQ;


/**
 * Class Consumer
 * 消费者
 * @package Libs\MyRabbitMQ
 */
class Consumer extends Parenter {

    /**
     * Parenter constructor.
     * @param array  $config
     * @param        $exchangeName
     * @param        $queueName
     * @param        $routeKey
     * @param string $exchangeType
     */
    public function __construct($config = array(),$exchangeName, $queueName, $routeKey, $exchangeType){
        parent::__construct($config,$exchangeName, $queueName, $routeKey, $exchangeType);
    }

    /**
     * 处理消息的回调方法
     * @param $msg
     */
    public function doProcess($msg){

        file_put_contents("d:/test_consumer_1.log", date('Y-m-d H:i:s').$msg.PHP_EOL,FILE_APPEND);
        echo $msg."\n";
    }
}
