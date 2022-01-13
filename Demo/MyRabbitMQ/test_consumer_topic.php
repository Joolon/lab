<?php
set_time_limit(0);

include_once '../../index.php';
include_once BASE_PATH.'/vendor/autoload.php';

use \Libs\MyRabbitMQ\Consumer;

$rb_conf      = require 'config.php';
$rb_conf      = $rb_conf['host'];
$exchangeName = 'EX_TOPIC';//交换机名（与交换机对应）
$queueName    = 'topic_1234'; //队列名称

// 两种特殊字符“*”与“#”：用于做模糊匹配，其中“*”用于匹配一个单词，“#”用于匹配多个单词（可以是零个、一个）
$routingKey   = 'one.topic.end';//路由关键字(与交换机对应，也可以省略)

// 写一个sell 脚本 以及while 循环来处理
// 死循环  再写一个守护进程监控
$consumer = new Consumer($rb_conf,$exchangeName,$queueName,$routingKey,AMQP_EX_TYPE_TOPIC);
//$consumer->run(false);
$consumer->run(true);


echo 'sss_mq';exit;