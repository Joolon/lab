<?php
use \Libs\MyRabbitMQ\ProductMQ;
use \Libs\MyRabbitMQ\ConsumerMQ;

set_time_limit(0);


//try{
//    (new ConsumerMQ)->run();
//}catch(\Exception $exception){
//    var_dump($exception->getMessage());
//}


try{
    (new ProductMQ())->run();
}catch(\Exception $exception){
    var_dump($exception->getMessage());
}

echo 'sss_mq';exit;
