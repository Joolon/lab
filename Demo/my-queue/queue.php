<?php
use DevelopModel\RedisHandle;

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'index.php';

$redis = RedisHandle::getRedis();

for($i = 0;$i < 100;$i++){
    $redis->rpush("mylist",md5(time().mt_rand()));
}

echo 'Success';exit;


