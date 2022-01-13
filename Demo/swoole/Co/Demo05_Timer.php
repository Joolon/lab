<?php

namespace Demo\swoole\Co;

use Swoole;
use Swoole\Coroutine;

/**
 * 定时器 Timer
 * Class Demo03_Timer
 * @package Demo\swoole\Co
 */
class Demo05_Timer {

    /**
     * 定时器的使用
     */
    public function cookByCo(){
        // 定时器方法：
        // \Swoole\Event::defer($callback)  在程序结束之后执行回调函数（本次请求结束时运行）
        // \Swoole\Timer::tick($msec,$callback)  设置一个间隔时钟定时器，每隔 $msec 毫秒执行一次 $callback 函数
        // \Swoole\Timer::clearAll()  清除当前Worker进程内所有定时器
        // \Swoole\Timer::clear($timer_id)   清除当前Worker进程内指定ID的定时器
        // \Swoole\Timer::after($msec)  在指定的时间后执行函数，一次性定时器，执行完成后就会销毁
        // \Swoole\Timer::list() 返回定时器迭代器，可使用 foreach 遍历当前 Worker 进程内所有 timer 的 id
        // \Swoole\Timer::info($timer_id)  返回 timer 的信息
        // \Swoole\Timer::set()   设置定时器相关参数

        \Swoole\Timer::set([// 默认定时器在执行回调函数时会创建协程，可以单独关闭默认创建协程
            'enable_coroutine' => false,
        ]);

        \Swoole\Event::defer(function () {
            echo "defer 在程序执行完时执行.\n";
        });

        \Swoole\Timer::tick(2000, function (int $timer_id, $param1, $param2) {
            echo time()."tick  定时器ID # $timer_id, 每隔 2000ms.\n";
            echo time()."tick  参数1 is $param1, 参数2 is $param2.\n";

            \Swoole\Timer::tick(3000, function ($timer_id) {// 每一次执行上层的 tick 会增加一个这样的间隔时钟
                echo time()."tick  定时器ID #$timer_id, 每隔 3000ms.\n";
            });

        }, "A", "B");

        $timer = \Swoole\Timer::after(6000, function () {
            echo "after 在 20 秒之后执行一次，";
            echo "结束定时器.\n";
            \Swoole\Timer::clearAll();
        });

        $timerList = \Swoole\Timer::list();// 返回的 timer_id 是 整型数值列表
        var_dump($timerList);

        // 循环遍历获取定时器信息
        foreach ($timerList as $timer_id) {
            echo "定时器ID $timer_id.\n";
            var_dump(\Swoole\Timer::info($timer_id));
        }
    }
}