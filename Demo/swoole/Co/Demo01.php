<?php

namespace Demo\swoole\Co;

use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole;

class Demo01 {

    /**
     * 本任务下子任务 耗时分别是：2、4、10，
     *      如果是同步结构则需要 16 秒
     *      开启协程后 IO就变成异步结构，总耗时由完成耗时最长的子任务决定： 10 秒
     */
    public function cookByCo(){
        $startTime = time();

        // 开启一键协程化: https://wiki.swoole.com/#/runtime?id=swoole_hook_all
        Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);

        // 创建一个协程容器: https://wiki.swoole.com/#/coroutine/scheduler
        // 相当于进入厨房
        \Co\run(function () {
            $fds = array();

            // 等待结果: https://wiki.swoole.com/#/coroutine/wait_group?id=waitgroup
            $wg = new WaitGroup();// 协程任务记录器

            $result = [];// 保存数据的结果

            $wg->add();// 记录一个任务
            // 开启一个新的协程
            Coroutine::create(function () use ($wg, &$result) {
                echo "开始煲汤..." . PHP_EOL;
                sleep(2);
                echo "汤好了..." . PHP_EOL;


                // 装盘
                $result['soup'] = '一锅汤';
                print_r($result);
                $wg->done(); // 标记任务完成
            });


            $wg->add();// 记录一个任务
            // 开启一个新的协程
            Coroutine::create(function () use ($wg, &$result) {
                echo "开始煮饭..." . PHP_EOL;
                // 煮饭需要5分钟，所以我们不用在这里等饭煮熟，放在这里一会再来看看好了没有
                // 我们先去煲汤(协程切换)
//                sleep(4);

                for($i = 0;$i <= 1000000000;$i ++){
                    $sss = 1 + $i;
                    if($i % 10000 == 0){
                        echo $sss;
                    }
                }
                echo "饭熟了..." . PHP_EOL;

                // 装盘
                $result['rice'] = '一锅米饭';
                print_r($result);
                $wg->done(); // 标记任务完成
            });


            $wg->add();// 记录一个任务
            // 再开启一个新的协程
            Coroutine::create(function () use ($wg, &$result) {
                // 因为开启协程后，IO全是异步了，在此demo中每次遇到sleep都会挂起当前协程
                // 切换到下一个协程执行。
                echo "开始煎鱼..." . PHP_EOL;
                sleep(1);
                echo "放油..." . PHP_EOL;
                sleep(1);
                echo "煎鱼..." . PHP_EOL;
                sleep(1);
                echo "放盐..." . PHP_EOL;
                sleep(1);
                echo "红烧..." . PHP_EOL;
                sleep(6);
                echo "出锅..." . PHP_EOL;

                // 装盘
                $result['food'] = '鱼香肉丝';
                print_r($result);
                $wg->done();
            });


            $wg->wait();// 等待全部任务完成

            echo "准备吃饭..." . PHP_EOL;

            // 返回数据(上菜！)
            print_r($result);
        });

        print_r('总耗时：' . (time() - $startTime) . ' 秒钟');
    }
}