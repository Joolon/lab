<?php

namespace Demo\swoole\Co;

use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole\Coroutine\System;
use Swoole;

class Demo02 {

    /**
     * 本任务下子任务 耗时分别是：2、4、10，
     *      如果是同步结构则需要 16 秒
     *      开启协程后 IO就变成异步结构，总耗时由完成耗时最长的子任务决定： 10 秒
     */
    public function cookByCo(){
        $startTime = time();

        $sch = new \Co\Scheduler;
        $sch->set(['max_coroutine' => 3]);// 设置最大可创建的协程数。设置为2，下面启动两个协程 为何会报错? exceed max number of coroutine 2，难道要设置 n+1

        // 开启一键协程化: https://wiki.swoole.com/#/runtime?id=swoole_hook_all
        //Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
        \Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]);// v4.4+

        \Co\run(function () {// Co\run() 是创建了协程容器
            $wg = new \Swoole\Coroutine\WaitGroup();
            $result = [];
            $exit_status = 0;

            $wg->add();
            //启动第一个协程
            go(function () use ($wg, &$result,&$exit_status) {// go 是 Coroutine::create 的简写
                System::sleep(1);// 等待1秒

                try{
                    //启动一个协程客户端client，请求淘宝首页
                    $cli = new \Swoole\Coroutine\Http\Client('www.taobao11.com', 443, true);
                    $cli->setHeaders([
                        'Host' => 'www.taobao.com',
                        'User-Agent' => 'Chrome/49.0.2587.3',
                        'Accept' => 'text/html,applica12tion/xhtml+xml,application/xml',
                        'Accept-Encoding' => 'gzip',
                    ]);
                    $cli->set(['timeout' => 1]);
                    $cli->get('/index.php');

                    $result['taobao']['body'] = '$cli->body';
                    $result['taobao']['errCode'] = $cli->errCode;

                    $cli->close();

                    if($cli->errCode != 0){
                        exit(500);// 协程里面退出相当于是抛出一个异常
                    }

                }catch (\Swoole\ExitException $e){
                    $exit_status = $e->getStatus();
                }

                $wg->done();
            });

            $wg->add();
            //启动第二个协程
            go(function () use ($wg, &$result) {
                //启动一个协程客户端client，请求百度首页
                $cli = new \Swoole\Coroutine\Http\Client('www.alipay.com', 443, true);
                $cli->setHeaders([
                    'Host' => 'www.alipay.com',
                    'User-Agent' => 'Chrome/49.0.2587.3',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml',
                    'Accept-Encoding' => 'gzip',
                ]);
                $cli->set(['timeout' => 1]);
                $cli->get('/');

                $result['baidu']['body'] = '$cli->body';
                $result['baidu']['errCode'] = $cli->errCode;
                $cli->close();

                $wg->done();
            });

            //挂起当前协程，等待所有任务完成后恢复
            $wg->wait();


            //这里 $result 包含了 2 个任务执行结果
            var_dump($exit_status);
            var_dump($result);
        });


        print_r('总耗时：' . (time() - $startTime) . ' 秒钟');
    }
}