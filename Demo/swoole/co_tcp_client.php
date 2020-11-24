<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';

\Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

\Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//创建100个协程
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//此处产生协程调度，cpu切到下一个协程，不会阻塞进程
            $redis->get('key');//此处产生协程调度，cpu切到下一个协程，不会阻塞进程
        });
    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();




/*


*/