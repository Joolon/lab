<?php
use \Libs\MyRabbitMQ2\AsyncProduceMQ;

include_once '../../index.php';

$rb_conf     = require 'config.php';
$rb_exchange = 'amq.direct';
$rb_route    = '111111';


try{
    $asyncProduceMQ = (new AsyncProduceMQ($rb_conf['host'], $rb_exchange));
    for($i = 0; $i < 3000; $i++){
        $rb_msg      = 'product message '.'-'.$i;//.'-'.rand(10000, 99999);
        $asyncProduceMQ->send($rb_msg, $rb_route);
    }

}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
