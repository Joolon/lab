<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';


/**
 * TCP 服务器
 * 
 * 调用服务是根据端口号来区分的，所以服务器端口 和 客户端端口要一致
 * $fd 参数表示客户端连接的唯一识别码，通过该参数可以和客户端进行点对点的通信
 *     $fd 是如从 1 2 3 这样的自然数
 * 
 */


//创建Server对象，监听 127.0.0.1:9501端口
$serv = new Swoole\Server(SWOOLE_SERVER, 9501);


//监听连接进入事件
$serv->on('Connect', function ($serv, $fd) {
    echo "Client: Connect.\n";
});

//监听数据接收事件
$serv->on('Receive', function ($serv, $fd, $from_id, $data) {
	$message = ' serv:'.json_encode($serv).', fd:'.$fd.', from_id:'.$from_id.', data:'.$data;
	
    $serv->send($fd, "Server: ".$message);
});

//监听连接关闭事件
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

//启动服务器
$serv->start();


/*

客户端访问（  支持远程 IP 访问）
telnet 127.0.0.1 9501
hello
Server: hello

*/
