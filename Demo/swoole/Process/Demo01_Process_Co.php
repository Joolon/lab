<?php

namespace Demo\swoole\Process;

use Swoole;
use Swoole\Coroutine;
use Swoole\Table;
use Swoole\Process;
use Swoole\Timer;


/**
 * swoole 内置的高性能内存共享
 * Class Demo06_Swoole_Table
 * @package Demo\swoole\Co
 */
class Demo01_Process_Co {

    /**
     * 定时器的使用
     */
    public function cookByCo(){
        \Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]);// v4.4+


        $process = new Process(function ($proc) {
            $timer = Timer::tick(1000, function () use ($proc) {
                $socket = $proc->exportSocket();
                $socket->send("hello master ".date('Y-m-d H:i:s'));
                echo "child timer\n";
            });

            Timer::after(6500, function () use ($timer) {
                echo "\n\nAfter 在 6.5 秒之后执行一次，结束定时器.定时器ID：（{$timer}）\n";
                Timer::clear($timer);
            });
        }, false, 1, true);

        $process->start();


        // 创建协程容器
        \Co\run(function() use ($process) {
            Process::signal(SIGCHLD, static function ($sig) {
                /*
                 * Swoole\Process::wait(false) 回收 结束的子进程
                 */
                while ($ret = Swoole\Process::wait(false)) {
                    /* clean up then event loop will exit */
                    Process::signal(SIGCHLD, null);
                    Timer::clearAll();
                }
            });
            /* your can run your other async or coroutine code here */
            Timer::tick(500, function () {
                echo "parent timer\n";
            });

            $socket = $process->exportSocket();
            while (1) {
                echo "父进程接收子进程消息：".$socket->recv();
                echo "\n\n";
            }
        });

    }
}