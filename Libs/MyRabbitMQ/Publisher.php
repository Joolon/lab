<?php

namespace Libs\MyRabbitMQ;

/**
 * Class Publisher
 * 生产者
 * @package Libs\MyRabbitMQ
 */
class Publisher extends Parenter {

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

    public function doProcess($msg){

    }

}
