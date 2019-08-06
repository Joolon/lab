<?php
use \Libs\MyRabbitMQ2\AsyncConsumerMQ;

set_time_limit(0);
include_once '../../index.php';


/**
 * Class ConsumerClient1
 */
class ConsumerClient2{

    public function processMessage($envelope, $queue) {
        $msg = $envelope->getBody();
        $envelopeID = $envelope->getDeliveryTag();
        file_put_contents("d:/test_consumer_2.log", date('Y-m-d H:i:s').$msg.'|'.$envelopeID.PHP_EOL,FILE_APPEND);
        $queue->ack($envelopeID);// 手动发送 ACK
    }
}
$client2 = new ConsumerClient2();



$rb_conf     = require 'config.php';
$rb_exchange = 'amq.direct';
$rb_queue    = 'fanout_1';


try{
    (new AsyncConsumerMQ($rb_conf['host'],$rb_exchange))->run(array($client2,'processMessage'),$rb_queue);
}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
