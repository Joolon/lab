<?php
set_time_limit(0);

include_once '../../index.php';
include_once BASE_PATH.'/vendor/autoload.php';

//include_once BASE_PATH.'/Demo/pcntl/test_pcntl.php';

use \Libs\MyRabbitMQ\Publisher;

$rb_conf      = require 'config.php';
$rb_conf      = $rb_conf['host'];
$exchangeName = 'EX_FANOUT';//交换机名
$queueName    = 'test_1234'; //队列名称（可以不设置队列名，只创建交换机，如果没有已经绑定的队列发送的数据将丢失）
$queueName    = null;
$routingKey   = 'PURCHASE_ORDER_INNER_ON_WAY_R_KEY';//路由关键字(也可以省略)

$publisherMQ = new Publisher($rb_conf,$exchangeName,$queueName,$routingKey,AMQP_EX_TYPE_FANOUT);

for($i = 0; $i < 30; $i++){
    $msgBody = 'product message '.'-'.$i;//.'-'.rand(10000, 99999);
    $publisherMQ->sendMessage($msgBody);
}

$publisherMQ->closeConnect();
echo 'sss_mq';exit;
