<?php
use \Libs\MyRabbitMQ\ConsumerMQ;

set_time_limit(0);
include_once '../../index.php';


class A{

    function processMessage($envelope, $queue) {
        $msg = $envelope->getBody();
        $envelopeID = $envelope->getDeliveryTag();
        file_put_contents("d:/log123.log", $msg.'|'.$envelopeID.PHP_EOL,FILE_APPEND);
        $queue->ack($envelopeID);
    }
}

$a = new A();

try{
    (new ConsumerMQ)->run(array($a,'processMessage'),false);
}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
