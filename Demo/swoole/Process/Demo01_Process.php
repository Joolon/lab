<?php

namespace Demo\swoole\Process;

use Swoole;
use Swoole\Coroutine;
use Swoole\Table;
use Swoole\Process;

/**
 * swoole 内置的高性能内存共享
 * Class Demo06_Swoole_Table
 * @package Demo\swoole\Co
 */
class Demo01_Process {

    /**
     * 定时器的使用
     */
    public function cookByCo(){

        for ($n = 1; $n <= 5; $n++) {
            $process = new Process(function () use ($n) {
                echo 'Child #' . getmypid() . " start and sleep {$n}s" . PHP_EOL;
                sleep($n);
                echo 'Child #' . getmypid() . ' exit' . PHP_EOL;
            });
            $process->start();
        }
        for ($n = 5; $n--;) {
            $status = Process::wait(true);
            echo "Recycled #{$status['pid']}, code={$status['code']}, signal={$status['signal']}" . PHP_EOL;
        }
        echo 'Parent #' . getmypid() . ' exit' . PHP_EOL;
    }
}