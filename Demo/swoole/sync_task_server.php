<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';



/**


执行异步任务

Swoole 提供了异步任务处理的功能，可以投递一个异步任务到 TaskWorker 进程池中执行，不影响当前请求的处理速度。
另外需要设置 task 进程数量，可以根据任务的耗时和任务量配置适量的 task 进程。


*/
$serv = new Swoole\Server(SWOOLE_SERVER, 9505);

//设置异步任务的工作进程数量
$serv->set(array('task_worker_num' => 4));

//此回调函数在worker进程中执行
$serv->on('receive', function($serv, $fd, $from_id, $data) {
    //投递异步任务
    $task_id = $serv->task($data);
    echo "--->>> $data  注册异步任务成功: id=$task_id\n";
	
	$serv->send($fd, "--->>> $data  异步任务注册成功！");
});

//处理异步任务(此回调函数在task进程中执行)
$serv->on('task', function ($serv, $task_id, $from_id, $data) {
    echo "--->>> $data  新的异步任务[id=$task_id]".PHP_EOL;
    //返回任务执行的结果
	
	for($i = 0 ;$i < 1000 ; $i ++){
		echo "--->>> $data  "."  {$i}  ".date("Y-m-d H:i:s")."处理中...".PHP_EOL;
		sleep(1);
	}
	
    $serv->finish("$data -> OK");
});

//处理异步任务的结果(此回调函数在worker进程中执行)
$serv->on('finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});

$serv->start();