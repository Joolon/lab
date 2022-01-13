<?php
use DevelopModel\MemcacheHandle;

/**
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2018/9/19
 * Time: 19:29
 */

echo '<pre>';

// PHP 版本: 5.6.27 NTS
// Memcache下载 http://static.runoob.com/download/memcached-win64-1.4.4-14.zip
// PHP 拓展包下载 https://windows.php.net/downloads/pecl/releases/memcache/3.0.8/php_memcache-3.0.8-5.6-nts-vc11-x86.zip


//$memcache = new Memcache;             //创建一个memcache对象
//$memcache->connect('127.0.0.1', 11211) or die ("Could not connect"); //连接Memcached服务器

$memcache = MemcacheHandle::getMemcache();

if(MemcacheHandle::getError()){
    echo "<font color='red'>Error：".MemcacheHandle::getError(). '</font><br/>';
    exit;
}
$key_res1 = $memcache->getVersion();                                 // 获取 Memcache 的版本号

$key_res1 = $memcache->set('l_id_1', 25,0,10);                       // 添加一个key-value
$key_res1 = $memcache->set('l_name_1', '风',0,10);                   // 添加一个key-value
$key_res1 = $memcache->add('l_name_2', '雨',0,10);                   // 若key不存在添加一个key-value，否则不做操作
$key_res1 = $memcache->add('l_name_3', '交',0,10);                   // 若key不存在添加一个key-value，否则不做操作

$key_res1 = $memcache->replace('l_name_3', '加',0,10);               // 若key存在改变其值，否则不做操作
$key_res1 = $memcache->replace('l_name_not_exists', '加',0,10);      // 若key存在改变其值，否则不做操作

$key_res1 = $memcache->get('l_name_1');                              // 获取指定 KEY 的值                              // 获取 Memcache 的版本号
$key_res1 = $memcache->get(['l_name_1','l_name_3','l_name_not_exists','l_name_not_exists_1']);// 获取 多个 KEY 的值（返回存在的 KEY-VALUE 数组）
$key_res1 = $memcache->delete('l_name_33');                          // 删除指定的 KEY

$key_res1 = $memcache->append('l_name_3', 'l_name_3后面追加加加加加加加加加',0,10); // 若key存在则在其值  后面  追加内容，否则不做操作
$key_res1 = $memcache->prepend('l_name_3', '前前前前前前前前加追面前l_name_3',0,10);// 若key存在则在其值  前面  追加内容，否则不做操作


$key_res1 = $memcache->cas('l_name_3', '火火火火火',0,10,9);                        // 该 key 对应的值没有被其他客户端修改的情况下， 才能够将值写入（未通过测试）

$key_res1 = $memcache->get('l_id_1');           // 获取指定 KEY 的值

$key_res1 = $memcache->increment('l_id_1');     // 对指定 数字值 增加一个值
$key_res1 = $memcache->increment('l_id_1',3);   // 对指定 数字值 增加一个值（必须是数值类型的 KEY，增量可以是正数或负数）
$key_res1 = $memcache->decrement('l_id_1');     // 对指定 数字值 减去一个值
$key_res1 = $memcache->decrement('l_id_1',5);   // 对指定 数字值 减去一个值（必须是数值类型的 KEY，增量可以是正数或负数）
$key_res1 = $memcache->increment('l_id_2_n');   // 若键不存在，则不做操作


$key_res1 = $memcache->flush();// 删除 当前服务上所有数据

$key_res1 = $memcache->getStats();// 获取 Memcache 服务器的状态、版本号、连接数、统计数据等信息
$key_res1 = $memcache->getStats('items');
$key_res1 = $memcache->getStats('sizes');

$key_res1 = $memcache->getExtendedStats();          // 获取 连接池中 所有连接的状态信息
$key_res1 = $memcache->getServerStatus('127.0.0.1');// 获取服务器是否启动，0.未启动，非0.运行中




