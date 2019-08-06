<?php
include_once '../../index.php';
include_once BASE_PATH.'/vendor/autoload.php';


use \Libs\MyRabbitMQ\Publisher;

$rb_conf      = require 'config.php';
$rb_conf      = $rb_conf['host'];
$exchangeName = 'amq.direct';//交换机名
$queueName    = 'kd_sms_send_q'; //队列名称
$routingKey   = '111111';//路由关键字(也可以省略)

$publisherMQ = new Publisher($exchangeName,$queueName,$routingKey,'direct',$rb_conf);

for($i = 0; $i < 3000; $i++){
    $msgBody = 'product message '.'-'.$i;//.'-'.rand(10000, 99999);
    $publisherMQ->sendMessage($msgBody);
}

$publisherMQ->closeConnetct();
echo 'sss_mq';exit;
