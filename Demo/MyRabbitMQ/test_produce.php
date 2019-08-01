<?php
use \Libs\MyRabbitMQ\ProduceMQ;

include_once '../../index.php';

$conf     = require 'config.php';
$exchange = 'amq.direct';
$message  = 'product message '.rand(10000, 99999);

try{
    (new ProduceMQ($conf['host'],$exchange))->send($message,'111111');
}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
