<?php
use \Libs\MyRabbitMQ\ProduceMQ;

include_once '../../index.php';


try{
    (new ProduceMQ())->run();
}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
