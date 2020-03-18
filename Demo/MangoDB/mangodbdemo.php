<?php
use DevelopModel\MongoHandle;

/**
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2018/12/14 0014
 * Time: 21:28
 */

/*
 * RDBMS  关系型数据库管理系统
 *      关系型数据库遵循的原则：ACID (原子性、一致性、独立性、持久性)
 *      A（Atomicity）原子性：事务里的操作要么全部执行，要么全都不执行。只要有一个失败，事务就会回滚。（记录全部操作的逆操作，当发生异常时回滚执行全部逆操作）
 *      C（Consistency）一致性：事务的执行不会破坏数据原有的完整性约束
 *      I（Isolation）独立性：并发事务之间不会互相影响，只要一个事务没有被提交，其他事务访问的数据就不会受到未提交事务的影响。
 *      D（Durability）持久性：事务提交后所做的修改都会永久的保存到数据库中，即使出现宕机（死机）也不会丢失。
 *
 * 特点：
 *      数据存储在单独的表中
 *      结构化组织化的数据
 *      结构化查询语句
 *      支持简单的事务
 */

/*
 * CAP定理：对于一个分布式系统来说，不可能同时很好的满足一致性、可用性和分割容忍 三个需求，最多只能满足两个。
 *      因此分为  CA原则，CP原则，AP原则
 *      C（Consistency）一致性：所有节点在同一时间具有相同的数据
 *      A（Availability）可用性：保证响应每个请求，不论成功或失败
 *      P（Partition tolerance）分割容忍：系统中任意信息的丢失或失败不会影响系统继续运作
 *
 * 分布式系统：多台计算机和通信软件通过网络组成的系统
 *      分布式系统的优点：
 *      可靠性：其中一台计算机崩溃不会影响到其他服务器
 *      资源共享：不同计算机之间的数据资源可以共享
 *      可拓展性：可以任意的增加更多的机器
 *      更快的速度：拥有多台计算机的计算能力，比其他单一计算机系统效率快的多
 *      更高的性能：
 */

/*
 * NoSQL ：Not Only SQL  不仅仅是SQL
 *      即是非关系型数据库，适用于超大规模数据的存储，相较于SQL数据库，NoSQL能够很好地处理大数据
 *      NoSQL
 *
 * 特点：
 *      数据存储在文档中
 *      没有固定的模式
 *      没有声明查询语言
 *      高性能、高可用性、可伸缩性
      * CAP定理
 *
 * 优点：
 *      高可拓展性
 *      分布式计算
 *      低成本
 *      没有复杂的关系
 *      架构的灵活性
 *
 * 缺点：
 *      有限的查询功能
 *      没有标准化
 *
 * 分类：
 *      列存储
 *      文档存储
 *      XML存储
 *      键值对存储
 *      对象存储
 *      图存储
 *
 *
 */


/**
 * MangoDB
 *
 * MongoDB 是由C++语言编写的，是一个基于分布式文件存储的开源数据库系统。
 * 在高负载的情况下，添加更多的节点，可以保证服务器性能。
 * MongoDB 旨在为WEB应用提供可扩展的高性能数据存储解决方案。
 *
 * 数据操作：支持创建指定大小的集合（记录条数和空间），数据都是类似JSON的 BSON 格式
 * 原子操作：MangoDB不支持事务，任何场景都不能要求保证数据完整性，但是它的所有操作的是原子操作，要么操作成功要么操作失败，不会成功一半的情况。
 * 层级关系：数据库 -> 集合 -> 文档
 * 基本操作：增删集合、增删查改文档
 * 索引：索引可以大大提高查询效率，如果没有索引，MangoDB就必须扫描整个集合中的所有文件并选取符合条件的记录。
 *      扫描全集合的效率非常低，要花费几十秒钟甚至几分钟，对网站来说是非常致命的。
 *      （索引性能：1、针对很少读取的集合，不要使用索引；2、索引存储在RAM中所以确保索引的大小不超过内存限制）
 *
 * 聚合：指数据处理并返回结果，如计算平均值、求和、最大最小值、最前最后一个记录（相当于MYSQL里面的count,max,min等函数）
 * 复制（副本集）：复制为数据副本。一主一从，一主多从。
 *      如何保证主从数据的一致性：主节点记录所有操作的oplog，从节点【定期轮询获取主节点修改的日志】，然后去操作自己的副本，从而保证数据的一致性。
 *      【特性：所有写入操作都在主节点上，任何节点可作为主节点。】
 * 分片技术：满足存储海量数据的需求（磁盘空间不足），满足读写吞吐量巨大的需求。
 * 备份与恢复：备份（mangodump）、恢复（mangorestore）
 * 监控：查看mangodb的运行情况和性能。开始（mangostart）、停止（mangotop）
 * 关系：表示多个文档之间的联系。
 *      关系类型有：
 *          嵌入式关系：对象的不同数据都存储在一个文档中，方便获取和维护，但是数据量大时影响读写性能
 *          引用式关系：对象的不同数据存储在多个文档中，通过 ID 来建立联系。
 * 查询分析器：explain()
 * 正则表达式：支持
 * ObjectId（12字节唯一识别码）：_id 每个文档都有唯一的 _id 键来确保文档中数据的唯一性。相较于自动增加的主键，_id 的方式更适合多服务器间的同步。
 * 固定集合：具有固定长度的集合，增删查改的速度非常快，插入数据时自动淘汰最早的数据。
 * 管理工具：RockMongo （PHP5开发的Web管理工具，类似 phpMyAdmin）
 *
 *
 * 安装与使用
 * 搭建服务器：http://www.runoob.com/mongodb/mongodb-window-install.html  （若 MSI 文件安装不成功，尝试使用 ZIP 文件搭建）
 * 创建数据库目录：C:\>mkdir data    数据文件夹
 *                 C:\data>mkdir db  数据库文件夹
 * 启动服务：C:\MongoDB\bin\mongod --dbpath C:\data\db
 *           C:\MongoDB\bin\mongo.exe  客户端连接
 * 查看安装是否成功：db.version()  查看安装的版本号
 *
 */

/**
 * MYSQL 与 MangoDB数据同步：两边建立一样的数据库、表名、字段名（方便数据同步）
 *
 *
 *
 */


echo "<pre>";
set_time_limit(0);

$mongo = MongoHandle::getMongo();
if(MongoHandle::getError()){
    echo "<font color='red'>Error：".MongoHandle::getError(). '</font><br/>';
    exit;
}
//echo 1;exit;
$db_mongo = $mongo->demo1;
$collection = $db_mongo->createCollection('users');
$collection = $db_mongo->createCollection('users1');
$collection = $db_mongo->createCollection('users2');
$collection = $db_mongo->createCollection('users3');

$document = array(
    "title"       => "MongoDB",
    "description" => "database",
    "likes"       => 100,
    "url"         => "http://www.runoob.com/mongodb/",
    "by", "菜鸟教程"
);

try{

    $res = $collection->insert($document);// 插入文档
    $collection->update(array("title"=>"MongoDB"), array('$set'=>array("title"=>"MongoDB 教程")),array('multiple' => '1'));
    $collection->remove(array(),array('justOne' => false));// justOne=>true 删除所有

}catch(Exception $exception){
    echo $exception->getMessage();exit;
}

$cursor = $collection->find();
foreach ($cursor as $document) {
    echo $document["title"] . "\n";
}

print_r($cursor);

print_r($mongo);





