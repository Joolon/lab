<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';



/**


执行异步任务

Swoole 提供了异步任务处理的功能，可以投递一个异步任务到 TaskWorker 进程池中执行，不影响当前请求的处理速度。
另外需要设置 task 进程数量，可以根据任务的耗时和任务量配置适量的 task 进程。


*/




$userInfo = ['name'=>'jack','email'=>'jack@qq.com']; // 这是我们准备传递给swoole服务端的数据

// 创建swoole客户端
$client = new Swoole\Client(SWOOLE_SOCK_TCP);

if (!$client->connect(SWOOLE_SERVER, 9505, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}

// 发送 用户数据 到服务端
$client->send(json_encode($userInfo));

// 接收服务端返回的内容
echo $client->recv();

// 关闭客户端
$client->close();