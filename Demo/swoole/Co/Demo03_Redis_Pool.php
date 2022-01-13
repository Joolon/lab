<?php

namespace Demo\swoole\Co;

use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;
use Swoole\Runtime;

/**
 * Connection Pool 连接池，创建一次连接后在协程容器中的所有协程都可以使用连接，如果连接已经断开则会自动重连
 * Class Demo03_Redis_Pool
 * @package Demo\swoole\Co
 */
class Demo03_Redis_Pool {

    private $_N = 1024;

    /**
     * Redis连接池
     */
    public function cookByCo(){

        Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
        $s = microtime(true);

        \Co\run(function () {
            $pool = new RedisPool((new RedisConfig)
                ->withHost('127.0.0.1')
                ->withPort(6379)
                ->withAuth('')
                ->withDbIndex(0)
                ->withTimeout(1)
            );
            for ($n = $this->_N; $n--;) {
                go(function () use ($pool) {
                    $redis = $pool->get();
                    $result = $redis->set('foo', 'bar');
                    if (!$result) {
                        throw new RuntimeException('Set failed');
                    }
                    $result = $redis->get('foo');
                    if ($result !== 'bar') {
                        throw new RuntimeException('Get failed');
                    }
                    $pool->put($redis);
                });
            }
        });
    }
}