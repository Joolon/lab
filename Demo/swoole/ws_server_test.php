<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';


// echo 1;exit;

?>


<html>

<body>

</body>

<script lang="javascript">


var wsServer = 'ws://47.107.183.46:9504/test?a=123';

alert(wsServer);
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("Connected to WebSocket server.");
};

// 与 websocket 服务器 onClose 事件对应，服务器执行事件会通知到这里执行相应操作
websocket.onclose = function (evt) {
    console.log("Disconnected");
};

// 与 websocket 服务器 onMessage 事件对应，服务器执行事件会通知到这里执行相应操作
websocket.onmessage = function (evt) {
    console.log('Retrieved data from server: ' + evt.data);
};

// 与 websocket 服务器 onError 事件对应，服务器执行事件会通知到这里执行相应操作
websocket.onerror = function (evt, e) {
    console.log('Error occured: ' + evt.data);
};


</script>

</html>