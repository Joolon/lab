<?php
use Db\UPdo;

/**
 * SQLite3：
 * SQLite3：SQLite是一个软件库，实现了自给自足的、无服务器的、零配置的、事务性的 SQL 数据库引擎。
 *      经济性：无需单独的数据库服务器，低成本，于对并发数量要求不高的本地服务
 *      效率性：占用资源很小
 *      可靠性：数据存储在本地服务器上，数据库就是一个文件，省去了网络连接开销
 *      独立性：只能本地嵌入，无法被远程的客户端访问，主要为本地程序提供数据存储服务
 *      简单性：功能简单，没有管理工具、分析工具等
 *
 * 主要应用于小客户量访问（百万次以下）的WEB服务、手机、 PDA 、机顶盒、嵌入式程序、物联网以及其他独立的客户端程序
 *
 * SQLite适用于小型网站、客户端；MySQL/Oracle等C/S数据库一般用来提供大数据、高并发的数据服务。
 *
 *
 * PDO：
 * PHP 数据对象 （PDO ：PHP Data Objects） 扩展为PHP访问数据库定义了一个轻量级的一致接口。
 * PDO 提供了一个数据访问抽象层，这意味着，不管使用哪种数据库，都可以用相同的函数（方法）来查询和获取数据。
 *
 *
 * MySQLi：
 * MySQLi 是 MySQL 的一个升级版本；MySQL是非永久性连接，每次连接都会打开一个新连接；MySQLi 是永久性连接，每次打开会使用连接池中的连接，可以减轻服务器的压力。
 * 永久性链接就是连接池，建立连接之后同一服务器同一用户访问同一数据库就会使用连接池中的连接，不会重新建立连接。
 *      （如果有大量用户访问就要增加 max_connections 参数，如果是 mysql_connect 非永久性连接就不用考虑这个参数了。）
 * MySQLi 可以支持 面向过程和面向对象的方式操作数据库，MySQL 只支持面向过程。
 *
 */



//$uPDO = UPdo::getInstance();
//$pdoStatement = $uPDO->query('SELECT * from pur_order_old limit 4');
//print_r($pdoStatement->fetchAll(\PDO::FETCH_ASSOC));exit;


$conn = new mysqli('localhost', 'root', 'root', 'demo');
$sql = "select * from pur_order_old limit 4";
$query = $conn->query($sql);
// fetch_assoc 关联数组展示
//print_r($query->fetch_object());exit;
while($row = $query->fetch_array()){
    print_r($row);
}

echo 'sss';exit;
