<?php
set_time_limit(0);

include_once '../../index.php';
include_once BASE_PATH.'/vendor/autoload.php';

use \Libs\MyRabbitMQ\Consumer;

$rb_conf      = require 'config.php';
$rb_conf      = $rb_conf['host'];
$exchangeName = 'EX_FANOUT';//交换机名

// 将6个队列绑定到 指定路由的交换机
// 队列与交换机绑定：
//      1、可以再创建交换机的时候绑定队列，发送到EXCHANGE的数据会路由到队列中；
//      2、也可以由消费者从对接取数据的时候创建队列并绑定到交换机，但是再绑定之后才会接收到EXCHANGE路由过来的消息
$queueName    = 'test_1'; //队列名称
$queueName    = 'test_12'; //队列名称
$queueName    = 'test_123'; //队列名称
$queueName    = 'test_1234'; //队列名称
$queueName    = 'test_12345'; //队列名称
$queueName    = 'test_123456'; //队列名称
$queueName    = 'test_1234567'; //队列名称
$routingKey   = 'PURCHASE_ORDER_INNER_ON_WAY_R_KEY';//路由关键字(也可以省略)

// 写一个sell 脚本 以及while 循环来处理
// 死循环  再写一个守护进程监控
$consumer = new Consumer($rb_conf,$exchangeName,$queueName,$routingKey,AMQP_EX_TYPE_FANOUT);
//$consumer->run(false);
$consumer->run(true);


echo 'sss_mq';exit;