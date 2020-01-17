<?php
set_time_limit(0);

include_once '../../index.php';
include_once BASE_PATH.'/vendor/autoload.php';

//include_once BASE_PATH.'/Demo/pcntl/test_pcntl.php';

use \Libs\MyRabbitMQ\Publisher;

$rb_conf      = require 'config.php';
$rb_conf      = $rb_conf['host'];
$exchangeName = 'amq.fanout';//交换机名
$queueName    = 'test_2'; //队列名称
$routingKey   = 'test_*';//路由关键字(也可以省略)

$publisherMQ = new Publisher($rb_conf,$exchangeName,$queueName,$routingKey,'fanout');

for($i = 0; $i < 300; $i++){
    $msgBody = 'product message '.'-'.$i;//.'-'.rand(10000, 99999);
    $publisherMQ->sendMessage($msgBody);
}

$publisherMQ->closeConnect();
echo 'sss_mq';exit;
