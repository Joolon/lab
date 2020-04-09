<?php
use Db\UPdo;

/**
 * SQLite：SQLite是一个软件库，实现了自给自足的、无服务器的、零配置的、事务性的 SQL 数据库引擎。
 *
 *      经济性：无需单独的数据库服务器，低成本，于对并发数量要求不高的本地服务
 *      效率性：占用资源很小
 *      可靠性：数据存储在本地服务器上，数据库就是一个文件，省去了网络连接开销
 *      独立性：只能本地嵌入，无法被远程的客户端访问，主要为本地程序提供数据存储服务
 *      简单性：功能简单，没有管理工具、分析工具等
 *
 * 主要应用于小客户量访问（百万次以下）的WEB服务、手机、 PDA 、机顶盒、嵌入式程序、物联网以及其他独立的客户端程序
 *
 *
 * SQLite适用于小型网站、客户端；MySQL/Oracle等C/S数据库一般用来提供大数据、高并发的数据服务。
 */

/**
 * PDO
 * PHP 数据对象 （PDO） 扩展为PHP访问数据库定义了一个轻量级的一致接口。
 * PDO 提供了一个数据访问抽象层，这意味着，不管使用哪种数据库，都可以用相同的函数（方法）来查询和获取数据。
 */


$uPDO = UPdo::getInstance();

$uPDO = UPdo::getInstance();
$uPDO = UPdo::getInstance();

foreach ($uPDO->query('SELECT * from pur_order_old limit 2') as $row) {
    print_r($row); //你可以用 echo($GLOBAL); 来看到这些值
}

print_r($uPDO);