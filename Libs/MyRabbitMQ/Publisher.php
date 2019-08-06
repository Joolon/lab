<?php

namespace Libs\MyRabbitMQ;


class Publisher extends Parenter {

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

    public function doProcess($msg){

    }

}
