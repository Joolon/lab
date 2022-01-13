<?php

/**
 * 多进程获取用户信息实例
 *
 * @author  salmonl <wcblks@gmail.com>
 * @since   2019-10-16
 */
class Demo03
{
    private $uids = [];
    private $workers = [];

    public function __construct($uids)
    {
        $this->uids = $uids;
    }

    /**
     * 启动多个进程执行任务
     */
    public function exec()
    {
        try {
            $count = count($this->uids);
            for ($i = 0; $i < $count; $i++) {
                $uid = $this->uids[$i];
                $process = new swoole_process(function (swoole_process $worker) use ($uid) {
// do task
                    $res = $this->doTask($uid);
                    $worker->write($res);
                }, true);
                $pid = $process->start();
                $this->workers[$pid] = $process;
            }
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    /**
     * 模拟获取用户信息
     *
     * @param int $uid 用户ID
     */
    protected function doTask($uid)
    {
        $res = [];
        switch ($uid) {
            case 100:
// get from api i.api.niliu.me/userinfo?uid=100
                $res = ['id' => 100, 'name' => 'salmonl', 'age' => 30, 'sex' => 'male'];
                break;
            case 200:
                $res = ['id' => 200, 'name' => 'tom', 'age' => 38, 'sex' => 'male'];
                break;
            case 300:
                $res = ['id' => 300, 'name' => 'bob', 'age' => 24, 'sex' => 'female'];
                break;
            default:
                break;
        }
// 休眠1秒
        sleep(1);
        return json_encode($res);
    }

    /**
     * 输出
     *
     * @param
     */
    public function output()
    {
        foreach ($this->workers as $worker) {
            $user_info = json_decode($worker->read(), true);
            $names[] = $user_info['name'];
        }

// 回收子进程
        while ($res = swoole_process::wait()) {
            echo PHP_EOL, 'Worker Exit, PID: ' . $res['pid'] . PHP_EOL;
        }

        echo json_encode($names), PHP_EOL;
    }
}

$uids = [100, 200, 300];

$stime = microtime(true);
$multi = new Multi_Process($uids);
$multi->exec();
$multi->output();
$etime = microtime(true);

echo 'exec time : ', round(($etime - $stime), 3), PHP_EOL;