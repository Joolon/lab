<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';


/**
 * UDP 服务器
 * 
 * UDP 与 TCP 不同，UDP没有连接的概念，
 * 
 */
 
 
//创建Server对象，监听 127.0.0.1:9502端口，类型为SWOOLE_SOCK_UDP
$serv = new Swoole\Server(SWOOLE_SERVER, 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP); 

//监听数据接收事件
$serv->on('Packet', function ($serv, $data, $clientInfo) {
    $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);
    var_dump($clientInfo);
});

//启动服务器
$serv->start(); 


/*

客户端访问（  支持远程 IP 访问）
netcat -u 127.0.0.1 9502
hello
Server: hello

*/