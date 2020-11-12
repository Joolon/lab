<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';

?>


<html>

<body>
<canvas id="canvasBarrage" class="canvas-barrage"></canvas>
<!--<video id="videoBarrage" width="960" height="540" src="./video/video.mp4" controls></video>-->
<p>
    <input class="ui-input" id="msg" name="value" value="发送弹幕" required>
    <input class="ui-button" type="button" id="sendBtn" value="发送弹幕">
</p>
</body>

<script lang="javascript">

    var wsServer = 'ws://127.0.0.1:9504';
    var websocket = new WebSocket(wsServer);
    websocket.onopen = function (evt) {// 建立连接
        console.log("创建连接.");
    };

    websocket.onclose = function (evt) {// 关闭连接
        console.log("连接失败");
    };

    websocket.onmessage = function (evt) {// 接收服务器推送的消息
        console.log('接收服务器消息: ' + evt.data);
    };

    websocket.onerror = function (evt, e) {
        console.log('发生错误: ' + evt.data);
    };

    var msg;
    var sendBtn = document.getElementById('sendBtn');
    sendBtn.onclick = function(){
        if (websocket.readyState === 1) {
            msg = document.getElementById('msg').value;
            alert(msg);
            websocket.send(msg);// 发送消息到服务器
        } else {
            alert('WebSocket 连接失败');
        }
    };

</script>

</html>