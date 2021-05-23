<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';


/**
 * WebSocket 服务器
 * 
 * 
 * 
 * 
 */
 
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new Swoole\WebSocket\Server(SWOOLE_SERVER, 9504);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    var_dump($request->fd, $request->get, $request->server);
    $ws->push($request->fd, "hello, welcome\n");
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
	
	while(1){
		sleep(1000);
		
		$ws->push($frame->fd, "server: onMessage".date('Y-m-d H:i:s'));
		
	}
	
	
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();



/*


*/