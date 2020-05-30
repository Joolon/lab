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
    echo "Dispatch AsyncTask: id=$task_id\n";
});