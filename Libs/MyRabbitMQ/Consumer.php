<?php

namespace Libs\MyRabbitMQ;


class Consumer extends Parenter {

    /**
     * Parenter constructor.
     * @param        $exchangeName
     * @param        $queueName
     * @param        $routeKey
     * @param string $exchangeType
     * @param array  $config
     */
    public function __construct($exchangeName, $queueName, $routeKey, $exchangeType = 'direct', $config = array()){
        parent::__construct($exchangeName, $queueName, $routeKey, $exchangeType, $config);
    }

    /**
     * 处理消息的回调方法
     * @param $msg
     */
    public function doProcess($msg){

        file_put_contents("d:/test_consumer_1.log", date('Y-m-d H:i:s')."\r ".$msg.PHP_EOL,FILE_APPEND);
        echo $msg."\n";
    }
}
