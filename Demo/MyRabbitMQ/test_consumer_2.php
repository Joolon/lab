<?php
set_time_limit(0);

include_once '../../index.php';
include_once BASE_PATH.'/vendor/autoload.php';

use \Libs\MyRabbitMQ\Consumer;

$rb_conf      = require 'config.php';
$rb_conf      = $rb_conf['host'];
$exchangeName = 'amq.direct';//交换机名
$queueName    = 'kd_sms_send_q'; //队列名称
$routingKey   = '111111';//路由关键字(也可以省略)

class Consumer2 extends Consumer{

    public function doProcess($msg){

        file_put_contents("d:/test_consumer_2.log", date('Y-m-d H:i:s').$msg.PHP_EOL,FILE_APPEND);
        echo $msg."\n";
    }
}
$consumer = new Consumer2($rb_conf,$exchangeName,$queueName,$routingKey,'direct');
//$consumer->run(false);
$consumer->run(true);


echo 'sss_mq';exit;