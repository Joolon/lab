WebSocket 是用户web浏览器和服务器之间进行任意的双向数据传输的一种技术。

准备工作：
1、安装拓展 extension=php_sockets.dll


WebSocket基于TCP协议

【
	拓展
	TCP：传输控制协议，是一种面向连接的、可靠的、基于字节流的传输层通讯协议。TCP保证数据正确性，通过TCP连接传送的数据，无差错，不丢失，不重复，且按序到达。
	数据帧：数据链路层的协议数据单元。
	
	TCP协议有三次握手和四次挥手。
		三次握手（1.客户端发送请求连接，2.服务器确认连接，3.客户端发送数据报文）
		四次挥手（1.客户端发送关闭连接，2.服务器确认收到请求，3.服务器发送连接释放报文，4.客户端发送关闭确认报文）
】

握手：浏览器向Websocket发送连接请求，服务器作出响应的过程。握手只需要一次就可以实现永久连接。（长连接，循环的连接不算）



循环的连接实现数据推送：通过 AJAX 每隔一秒钟向服务器发送一个 HTTP 请求来获取最新的数据。
	（缺点是：浏览器需要不断发送请求，HTTP请求可能含有很长的头部信息，实际有用的数据只有很小的一部分，所以这样浪费了很多宽带资源）
	
	
	
	
	php应用实现webSocket更多使用 workerman 或 swoole 框架.

