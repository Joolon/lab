<?php
use \Libs\MyRabbitMQ\ConsumerMQ;

set_time_limit(0);
include_once '../../index.php';


/**
 * Class ConsumerClient1
 */
class ConsumerClient1{

    public function processMessage($envelope, $queue) {
        $msg = $envelope->getBody();
        $envelopeID = $envelope->getDeliveryTag();
        file_put_contents("d:/log123.log", date('Y-m-d H:i:s')."\r ".$msg.'|'.$envelopeID.PHP_EOL,FILE_APPEND);
        $queue->ack($envelopeID);
    }
}

$client1 = new ConsumerClient1();

try{
    (new ConsumerMQ)->run(array($client1,'processMessage'),false);
}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
