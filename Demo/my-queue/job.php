<?php
use DevelopModel\RedisHandle;

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'index.php';

$redis = RedisHandle::getRedis();

for($i = 0;$i < 10;$i++){
    $data = $redis->lpop('mylist');
    if(empty($data)) break;

    file_put_contents('./log.txt',json_encode($data).PHP_EOL,FILE_APPEND);
}

echo 'Success';exit;


