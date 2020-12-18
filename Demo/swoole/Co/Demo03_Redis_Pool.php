<?php

namespace Demo\swoole\Co;

use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;
use Swoole\Runtime;

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