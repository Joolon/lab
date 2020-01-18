<?php
set_time_limit(0);

include_once '../../index.php';
include_once BASE_PATH.'/vendor/autoload.php';

//include_once BASE_PATH.'/Demo/pcntl/test_pcntl.php';

use \Libs\MyRabbitMQ\Publisher;

// 两种特殊字符“*”与“#”：用于做模糊匹配，其中“*”用于匹配一个单词，“#”用于匹配多个单词（可以是零个、一个）
//      1、单词可以是字母数字下划线的组合，只是用.号分隔
//      2、只要routingKey符合路由规则则会把数据发送到相关队列，不需要绑定队列到交换机（即是不管哪个交换机发送的消息 都可以根据routingKey映射到队列中）
//      3、一般交换机的routingKey是固定的，队列的routingKey才是需要模糊匹配接收数据

$rb_conf      = require 'config.php';
$rb_conf      = $rb_conf['host'];
$exchangeName = 'EX_TOPIC';//交换机名
$queueName    = null;
$queueName    = 'topic_1';
$queueName    = 'topic_12';
$queueName    = 'topic_123';
$queueName    = 'topic_1234';
$queueName    = 'topic_12345';
$queueName    = 'topic_123456';
$routingKey   = 'one.topic.end';//路由关键字(也可以省略，一般是固定的，不需要“*”、“#”符号)

$publisherMQ = new Publisher($rb_conf,$exchangeName,$queueName,$routingKey,AMQP_EX_TYPE_TOPIC);

for($i = 0; $i < 30; $i++){
    $msgBody = 'product message '.'-'.$i;//.'-'.rand(10000, 99999);
    $publisherMQ->sendMessage($msgBody);
}

$publisherMQ->closeConnect();
echo 'sss_mq';exit;
