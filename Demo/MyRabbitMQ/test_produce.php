<?php
use \Libs\MyRabbitMQ\AsyncProduceMQ;

include_once '../../index.php';

$rb_conf     = require 'config.php';
$rb_exchange = 'amq.direct';
$rb_msg      = 'product message '.rand(10000, 99999);
$rb_route    = '111111';

try{
    (new AsyncProduceMQ($rb_conf['host'],$rb_exchange))->send($rb_msg,$rb_route);
}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
