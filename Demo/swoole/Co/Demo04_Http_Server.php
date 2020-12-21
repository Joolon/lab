<?php

namespace Demo\swoole\Co;

use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole;

class Demo04_Http_Server {

    /**
     * 本任务下子任务 耗时分别是：2、4、10，
     *      如果是同步结构则需要 16 秒
     *      开启协程后 IO就变成异步结构，总耗时由完成耗时最长的子任务决定： 10 秒
     */
    public function cookByCo(){

        // 启动一个全协程 HTTP 服务
        \Co\run(function () {
            $server = new \Co\Http\Server(SWOOLE_SERVER, 9510, false);

            // 浏览器调用 http://127.0.0.1:9510
            $server->handle('/', function ($request, $response) {
                \Co\System::sleep(3);
                $response->end("<h1>Index</h1>");
            });
            // 浏览器调用 http://127.0.0.1:9510/test
            $server->handle('/test', function ($request, $response) {
                $response->end("<h1>Test</h1>");
            });
            // 浏览器调用 http://127.0.0.1:9510/stop
            $server->handle('/stop', function ($request, $response) use ($server) {
                $response->end("<h1>Stop</h1>");
                $server->shutdown();
            });
            $server->start();
        });
    }
}