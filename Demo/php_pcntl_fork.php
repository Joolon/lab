<?php
/**
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2021/6/16
 * Time: 10:12
 */

$dbms='mysql';     //数据库类型
$host='localhost'; //数据库主机名
$dbName='test';    //使用的数据库
$user='root';      //数据库连接用户名
$pass='root';          //对应的密码
$dsn="$dbms:host=$host;dbname=$dbName";

// 进程数量
$pro_count = 100;
$pids = [];
for ($i = 0; $i < $pro_count; ++$i) {
    $pid = pcntl_fork();
    if ($pid < 0) {
        // 主进程
        throw new Exception('创建子进程失败: ' . $i);
    } else if ($pid > 0) {
        // 主进程
        $pids[] = $pid;
    } else {
        // 子进程
        try {
            $pdo = new PDO($dsn,$user,$pass);
            $pdo->beginTransaction();
            $stmt = $pdo->query('select `count` from test');
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $count = intval($count);
            if ($count > 0) {
                $count--;
                $pdo->query('update test set `count` = ' . $count . ' where id = 2');
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        // 退出子进程
        exit;
    }
}