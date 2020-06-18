<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';



/**
 *
 * 执行异步任务
 * Swoole 提供了异步任务处理的功能，可以投递一个异步任务到 TaskWorker 进程池中执行，不影响当前请求的处理速度。
 * 另外需要设置 task 进程数量，可以根据任务的耗时和任务量配置适量的 task 进程。
 *
 * 常见问题：
 * 如果 当前任务池中的任务数量 大于 配置的进程数，那么服务器还会响应客户端请求，但是会给出警告提示：服务器处于满负荷状态。
 * [2020-05-30 17:08:16 #6435.1]	WARNING	swServer_master_onTimer (ERRNO 9007): No idle task worker is available
 * 当 服务器 执行完任务后会去执行 任务池中的其他任务，直到任务池中的任务都执行完毕
 *
 *
 *
 */




$taskName = "任务名->".substr(strtoupper(md5(time())),0,5); // 这是我们准备传递给swoole服务端的数据

// 创建swoole客户端
$client = new Swoole\Client(SWOOLE_SOCK_TCP);

if (!$client->connect(SWOOLE_SERVER, 9505, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}

// 发送 用户数据 到服务端
$client->send($taskName);

// 接收服务端返回的内容
echo $client->recv();
echo PHP_EOL;

// 关闭客户端
$client->close();

echo '已处理完成'.PHP_EOL;
exit;