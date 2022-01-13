<?php
/**
 * RabbitMQ Windows 安装 https://blog.csdn.net/weixin_39735923/article/details/79288578
 * 如提示版本不对，可能是之前按照的 Erlang没有删除干净
 *
 */
use DevelopModel\RedisHandle;

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'index.php';

$redis = RedisHandle::getRedis();

for($i = 0;$i < 100;$i++){
    $redis->rpush("mylist",md5(time().mt_rand()));
}

echo 'Success';exit;


