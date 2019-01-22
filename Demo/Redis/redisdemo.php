<?php
use DevelopModel\RedisHandle;


// Redis是什么？ Redis是一个完全开源免费的（C语言编写），遵循BSD协议的，高性能的Key-Value数据库。
// Redid所有命令集合  https://redis.io/commands  （PHP Redis类的方法名称对应 redis 的命令名）


// Redis的特点：
// 1、支持持久化（可将内存中的数据保存到磁盘中，重启的时候再次加载到内存中）
// 2、除支持Key-Value类型数据外，还支持list、set、zset、hash等结构的数据
// 3、支持master-slave 主从模式的备份

// Redis的优势：
// 1、性能极高，每秒支持11万次读，8.1万次写操作
// 2、丰富的数据类型
// 3、操作的原子性（要么成功要么啥都不做），支持事务
// 4、丰富的特性：支持publish/subscribe、通知、数据有效期


// PHP 版本: 5.6.27 NTS
// PHP 拓展下载地址 https://windows.php.net/downloads/pecl/releases/redis/2.2.7/   （拓展PHP版本必须与开发环境版本一致）
// Redis 服务器：https://github.com/MicrosoftArchive/redis/releases/tag/win-3.2.100


// 应用：
// 1、存储 SESSION 数据
// 2、RBAC 管理（基于角色的访问控制）


echo "<pre>";
set_time_limit(0);


$redis = RedisHandle::getRedis();
if(RedisHandle::getError()){
    echo "<font color='red'>Error：".RedisHandle::getError(). '</font><br/>';
    exit;
}
$redis->set('user:name','fasf12333da');
var_dump($redis->get('user:name'));
echo 1;exit;

//$redis = new Redis();
//$redis->connect('127.0.0.1', 6379);
////$redis->close();// 关闭 Redis 连接
//
//// 检测 redis 服务是否启动
//try {
//    $redis->auth('admin123');
//    $ping = $redis->ping();// 连通返回 +PONG
//    echo "Connection to server successfully<br/>";
//} catch (Exception $e) {
//    echo "<font color='red'>Error：".$e->getMessage() . '</font><br/>';
//    exit;
//}


$redis->echo('Hello World');// 未测试通过
$select_res = $redis->select(3);//默认选择 0号数据库，切换数据库 默认只有16( 0-15 )个数据库

$redis->flushDB();// 删除 当前数据库 中所有数据
//$redis->flushAll();// 删除 Redis服务器中所有数据库的所有数据


//$res = $redis->config('SET','requirepass','admin1');// 动态修改Redis服务器密码（修改密码后要重新连接）
$res = $redis->config('GET','requirepass');// 获取配置信息：密码


// Redis支持五种数据类型：string（字符串），hash（哈希），list（列表），set（集合）及zset(sorted set：有序集合)。


// 对键的操作
$redis->set('l_name1','风',10);
$redis->set('l_name2','火',10);
$redis->set('l_name3','雷',10);
$redis->set('l_name4','电',10);
$redis->set('l_name5','金',10);
$redis->set('l_name6','木',2);
$redis->set('l_name7','水',10);
$redis->set('l_name8','火',10);
$redis->set('l_name9','土',10);

$redis->set('l_id1',1,10);
$redis->set('l_id2',2,10);
$redis->set('l_id3',3,10);
$redis->set('l_id4',4,10);
$redis->set('l_id5',5,10);
$redis->set('l_id6',6,10);
$redis->set('l_id7',7,10);


// 对相似数据分组设置键 便于可视化工具管理
$redis->set('l_name:a1','木');// Redis Desktop Manager: l_name/ 生成文件夹
$redis->set('l_name:a2','木');
$redis->set('l_name:a3','木');
$redis->set('l_name:a4','木');
$redis->set('l_name:a5','木');
$redis->set('l_name:a6','木');

$redis->set('l_name:list:a1','水');// Redis Desktop Manager: l_name/list/ 生成文件夹
$redis->set('l_name:list:a2','水');
$redis->set('l_name:list:a3','水');
$redis->set('l_name:list:a4','水');
$redis->set('l_name:list:1:2:3:4:5:6:7:8:a5','水');
$redis->set('l_name:list:1:2:3:4:5:6:7:8:9:10:11:12:13:14:!5:16:17:a6','水');


$l_res1  = $redis->set('l_name1','风',10);// $timeout = 10s
$l_name1 = $redis->get('l_name1');

$redis->delete('l_name1','l_name2','l_name3');                      // 删除键 无返回值
$l_res2  = $redis->del('l_name1','l_name2','l_name3');              // 返回被删除 key 的数量
$l_res2  = $redis->del(['l_name1','l_name2','l_name3','l_name4']);  // 批量删除
$l_name1 = $redis->get('l_name1');
$l_res11 = $redis->exists('l_name1');                               // 键名是否存在
$l_res11 = $redis->exists('l_name5');
$l_res11 = $redis->expire('l_name5',1);                             // 设置键的有效期，单位秒：键存在true,键不存在false
$l_res11 = $redis->expireAt('l_name5',1537414757);                  // 设置键的有效期：指定时间戳之前有效，过后失效（注意到时间后已被删除）
$l_res11 = $redis->expireAt('l_name5',time()+2);
// expireAt过了指定的时间  已被删除
$l_res11 = $redis->pExpire('l_name5',1005);                         // 设置键的有效期，单位毫秒：指定时间戳之前有效，过后失效
$l_res11 = $redis->pExpireAt('l_name5',1537414757002);              // 设置键的有效期，毫秒级的时间戳 ( 13位长度 )
$l_res11 = $redis->exists('l_name5');
$l_res11 = $redis->pttl('l_name6');                                 // 获取键剩余的过期时间，单位毫秒（ 返回值：-2.键不存在，-1.未设置过期时间长期有效，>=0.生命值 毫秒）
$l_res11 = $redis->persist('l_name6');                              // 移除键的过期时间，持久有效
$l_res11 = $redis->pttl('l_name6');
$l_res11 = $redis->ttl('l_name6');                                  // 获取键剩余的过期时间，单位秒（四舍五入）
$l_res11 = $redis->rename('l_name7','l_name77');                    // 重命名 键名
$l_res11 = $redis->renameNx('l_name8','l_name777');                 // 仅当 新键名不存在时 重命名，否则 失败
$l_res11 = $redis->type('l_name9');                                 // 获取指定键名的值得类型 测试 没通过


// String 字符串： redis 最基本的类型，其值可以是任何类型的数据，图片或者序列号的对象等等。string 类型的值最大能存储 512MB。
// String 在 redis内部存储默认是字符串，当使用 incr、decr 等函数操作时会转换成数值类型进行计算，操作后数据类型也会变成 int 类型
// 应用场景：常规key-value缓存应用。常规计数: 粉丝数，库存数等计数
$l_res11 = $redis->set('key1','abcdefgh');
$l_res11 = $redis->get('key1');
$l_res11 = $redis->getRange('key1',0,3);                            // 从字符串中获取指定位置的子字符串
$l_res11 = $redis->getRange('key1',0,-1);                           // 返回整个字符串
$l_res11 = $redis->getSet('key1','abcdefghijklmnopqrstuvwxyz');     // 设置值并返回旧值
$l_res11 = $redis->mget(['l_id1','l_id2','l_id3']);                 // 获取多个键的值 返回所有键值的 索引数组
$l_res11 = $redis->get('l_id1');
$l_res11 = $redis->incr('l_id1');                                   // 自增 1
$l_res11 = $redis->incrBy('l_id1',5);                               // 增加 指定值
$l_res11 = $redis->decr('l_id1');                                   // 自减 1
$l_res11 = $redis->decrBy('l_id1',4);                               // 减少 指定值

// 其他各种操作 参考手册

// 哈希表：散列表（Hash table，也叫哈希表），是根据关键码值(Key value)而直接进行访问的数据结构，相当于关联数组。


// Hash 哈希：hash是键值对的集合，是一个string类型的field和value的映射表，hash特别适合用于存储对象
// Hash 应用场景：存储用户信息、订单信息（即对象：拥有多个属性）
// 假设要存储一个用户的基本信息：用户唯一标识：ID，用户属性：姓名、年龄、性别
// 方式一：将ID作为Key,用户属性序列化转成字符串作为value。（这种查看数据时必须把整个对象数据取回，修改一项数据时反序列化/序列化增加了开销）
// 方式二：将ID + 属性名 作为Key，属性值作为value。（这种虽然省去了序列化/反序列化的开销，但是ID是重复的，当有大量用户数据时浪费了大量内存）
// 方式三：使用Hash，将ID作为Key，属性名和值是一个map的value，map的key是属性名，value是属性值。（通过 ID + map的key 即可操作map的value，避免了数据重复也免去了序列化/反序列化的开销 ）

// 一个键 的值 对应一个哈希表
// A。操作数组
$h_arr1 = ['username' => 'fairy','age' => 2000,'sex' => '2'];
$h_arr2 = ['username' => 'wee','age' => 2000,'sex' => '1'];
$h_arr3 = ['username' => 'safe'];
$h_arr4 = ['username' => 'alpha'];

$h_res1 = $redis->del(['h_user1','h_user2','h_user3','h_user4']);   // 删除 哈希表
$h_res1 = $redis->hMset('h_user1',$h_arr1);                         // 哈希表存储数组
$h_res1 = $redis->hMset('h_user2',$h_arr2);
$h_res1 = $redis->hMset('h_user3',$h_arr3);


$h_res1 = $redis->hSet('h_user3','age' ,1888);                      // 设置 哈希表 指定字段的值
$h_res1 = $redis->hSetNx('h_user3','address' ,'深圳市龙岗区');       // 设置 哈希表 不存在的字段的值（已存在的字段不做操作）
$h_res1 = $redis->hMset('h_user3',['age' => 1999,'sex' => 2]);      // 改变 哈希表 多个字段的值
$h_res1 = $redis->hGet('h_user1','username');                       // 获取哈希表 的一个字段
$h_res1 = $redis->hMGet('h_user1',['username','age']);              // 获取指定哈希表 的指定多个字段
$h_res1 = $redis->hGetAll('h_user1');                               // 获取哈希表所有的字段和值
$h_res1 = $redis->hKeys('h_user2');                                 // 获取哈希表 的所有字段（不包括值）
$h_res1 = $redis->hVals('h_user1');                                 //  获取哈希表所有字段的值（不包字段名）
$h_res1 = $redis->hDel('h_user1','sex','age');                      // 删除哈希表 的一个或多个字段
$h_res1 = $redis->hExists('h_user1','age');                         // 判断哈希表 字段是否存在
$h_res1 = $redis->hIncrBy('h_user2','sex',2);                       // 指定字段 增加一个整数值（不是整数将不会改变值）
$h_res1 = $redis->hIncrByFloat('h_user2','sex',3.3);                // 指定字段 增加一个整数或浮点数值
$h_res1 = $redis->hLen('h_user2');                                  // 获取哈希表中字段的个数

// B。操作对象（hash怎么存储对象呢？）
$man = new stdClass();
$man->username = 'aaa';
$man->age = 12;
// 字符类型存储 序列化的对象（反序列化后可获得对象以及属性值）
$h_res1 = $redis->set('h_man1',serialize($man));// 获取哈希表所有字段的值（不包字段名）
$h_res1 = $redis->get('h_man1');// 获取哈希表 的一个字段
$h_res1 = unserialize($h_res1);



// List 列表：列表是简单的字符串列表，按照插入顺序排序
// 应用场景：消息队列（无优先级）、         sorted set可以构建有优先级的队列系统
// 队列原理：多个线程写入数据到Redis中，然后worker统一将所有数据写入到数据库中。
$list_res1 = $redis->del(['user_list','user_list_2']);
$list_res1 = $redis->lPush('user_list','a');                        // 插入值到列表头部
$list_res1 = $redis->lPush('user_list','b','c','sss');
$list_res1 = $redis->lPushx('user_list','t');                       // 插入一个元素 到 已存在的列表的 头部
$list_res1 = $redis->rPush('user_list','aaa','bbb');                // 插入多个元素到 已存在的列表的 底部
$list_res1 = $redis->rPushx('user_list','cccc');                    // 插入一个元素到 已存在的列表的 底部
$list_res1 = $redis->lPush('user_list_2','a');                      // 插入值到列表头部
$list_res1 = $redis->lPush('user_list_2','b','c','yyyy');

$list_res1 = $redis->lLen('user_list');                             // 获取列表长度
$list_res1 = $redis->lRange('user_list',0,-1);                      // 根据索引获取 列表（获取整个列表，以 -1 表示列表的最后一个元素）

$list_res1 = $redis->lPop('user_list');                             // 移除并获取列表的第一个元素
$list_res1 = $redis->rPop('user_list');                             // 移除并获取列表最后一个元素
$list_res1 = $redis->blPop(['user_list'],3);                        // （从列表头部）移除并获得第一个元素
$list_res1 = $redis->blPop(['user_list','user_list_2'],3);          // 从列表头部 移除并获得第一个元素
$list_res1 = $redis->brPop(['user_list'],3);                        // 从列 底部 移除并获得第一个元素
$list_res1 = $redis->brpoplpush('user_list','user_list_2',3);       // 移除 第一个列表底部的一个元素 并 插入到 第二个列表 的头部
$list_res1 = $redis->rpoplpush('user_list','user_list_2');          // 移除 第一个列表 底部第一个元素 并 插入到 第二个列表的头部（没有第三个参数 不等单时间 ，brpoplpush 方法等待）

$list_res1 = $redis->lIndex('user_list',2);                         // 根据索引获取列表的值（索引 说明是从头部开始计算，从0开始。列表都是索引数组）
$list_res1 = $redis->lInsert('user_list',Redis::BEFORE,'f','ggggg');// 在列表 指定元素 前插入一个元素ggggg
$list_res1 = $redis->lInsert('user_list',Redis::AFTER,'f','eeeee'); // 在列表 指定元素 后插入一个元素eeeee
$list_res1 = $redis->lRem('user_list','a',3);                       // 移除 列表中 指定个数 的 指定值得元素（返回移除元素的数量）
$list_res1 = $redis->lSet('user_list',2,'ffff');                    // 改变指定索引的元素的值
$list_res1 = $redis->lTrim('user_list',1,3);                        // 去除列表中 指定索引之外的元素




// Set 集合：Set是string类型的无序集合，集合内元素拥有唯一性
// Set 与 List 相似，Set可以排重保持唯一性，可以说Set是元素不重复的List
$redis->del(['school','school_985','school_211']);
$set_res1 = $redis->sAdd('school_985','清华');                                      // 向 集合添加一个或多个成员
$set_res1 = $redis->sAdd('school_985','北大','南开','中山');
$set_res1 = $redis->sAdd('school_211','清华','昌大','南航','中行','南医');

$set_res1 = $redis->sDiff('school_985','');                                         // 获取 集合所有成员
$set_res1 = $redis->sMembers('school_211');                                         // 获取 集合所有成员
$set_res1 = $redis->sCard('school_985');                                            // 计算 集合中元素的数量
$set_res1 = $redis->sDiff('school_985','school_211');                               // 返回两个集合的差集
$set_res1 = $redis->sDiffStore('school_985_211_diff','school_985','school_211');    // 返回两个集合的差集 并存储到 指定集合中（该集合如果存在则会被覆盖）
$set_res1 = $redis->sInter('school_985','school_211');                              // 返回两个集合的交集
$set_res1 = $redis->sInterStore('school_985_211_same','school_985','school_211');   // 返回两个集合的交集 并存储到 指定集合中（该集合如果存在则会被覆盖）
$set_res1 = $redis->sUnion('school_985','school_211');                              // 获取 多个集合的并集
$set_res1 = $redis->sUnionStore('school_985_211_union','school_985','school_211');  // 获取 多个集合的并集 并存储到 指定集合中（该集合如果存在则会被覆盖）

$set_res1 = $redis->sIsMember('school_985','北大青鸟');                             // 判断集合是否存在指定元素
$set_res1 = $redis->sMembers('school_985');                                         // 获取集合的所有成员
$set_res1 = $redis->sMove('school_211','school_985','昌大');                        // 从A集合（删除元素） 移动指定元素 到 B集合（新增元素）（操作保持原子性）
$set_res1 = $redis->sPop('school_211');                                             // 从集合中随机 移除并 获取一个元素
$set_res1 = $redis->sRandMember('school_211',2);                                    // 从集合中随机获取多个元素（不会删除元素）
$set_res1 = $redis->sRem('school_985','中山');                                      // 从集合 删除指定元素



// ZSet 有序集合：和Set类似，比无序集合多一个double类型的分数，根据这个分数实现排序。集合内元素拥有唯一性，但分数(score)却可以重复。
$redis->del(['school_score','school_985_score','school_211_score']);
$z_set_res1 = $redis->zAdd('school_985_score',1,'清华');// 向 有序集合添加一个或多个成员
$z_set_res1 = $redis->zAdd('school_985_score',3,'北大',2,'南开',4,'中山');
$z_set_res1 = $redis->zAdd('school_211_score',1,'清华',2,'昌大',3,'南航');

$z_set_res1 = $redis->zRank('school_985_score','北大');                      // 获取指定成员的索引
$z_set_res1 = $redis->zRange('school_985_score',0,-1);                      // 通过索引 获取 有序集合成员（所有成员）
$z_set_res1 = $redis->zCard('school_985_score');                            // 获取 有序集合成员个数
$z_set_res1 = $redis->zCount('school_985_score',0,5);                       // 获取 有序集合指定分数区间成员个数

$z_set_res1 = $redis->zIncrBy('school_985_score',3,'中山');                 // 有序集合 指定成员分数增加
$z_set_res1 = $redis->zCount('school_985_score',0,5);                       // 获取 有序集合指定分数区间成员个数
$z_set_res1 = $redis->zScore('school_985_score','中山');                    // 获取指定元素的分数值

$z_set_res1 = $redis->zRange('school_985_score',0,-1);                      // 分数从 低 到 高 排序后， 根据索引获取 集合的成员
$z_set_res1 = $redis->zRevRange('school_985_score',2,3);                    // 分数从 高 到 低 排序后， 根据索引获取 集合的成员
$z_set_res1 = $redis->zRevRange('school_985_score',0,-1);                   // 分数从 低 到 高 排序后， 根据索引获取 集合的成员
$z_set_res1 = $redis->zRangeByScore('school_985_score',1,10);               // 分数从 低 到 高 排序后，获取 有序集中指定分数区间内的成员
$z_set_res1 = $redis->zRevRangeByScore('school_985_score',10,1);            // 分数从 高 到 低 排序后，获取 有序集中指定分数区间内的成员
$z_set_res1 = $redis->zInter('school_985_211_score_same',['school_985_score','school_211_score']);// 获取两个 有序集合的交集

$z_set_res1 = $redis->zRange('school_985_score',0,-1);                      // 通过索引 获取 有序集合成员（所有成员）
$z_set_res1 = $redis->zDelete('school_985_score','中山');                   // 删除指定的元素
$redis->zDeleteRangeByRank('school_985_score',0,1);                         // 分数从 低 到 高 排序后，根据索引删除元素
$redis->zDeleteRangeByScore('school_985_score',2,4);                        //  分数从 低 到 高 排序后，根据分数范围删除 元素
$z_set_res1 = $redis->zRange('school_985_score',0,-1);                      // 通过索引 获取 有序集合成员（所有成员）


// HyperLogLog 基数：集合中所有 不同元素的集合
$redis->del('list_id_pf');
$numArr = [];
for($i = 0;$i < 30;$i ++){
    $numArr[] = rand(0,20);
}
$set_res_pf = $redis->pfAdd('list_id_pf',$numArr);// 添加指定元素到HyperLogLog中
$set_res_pf = $redis->pfCount('list_id_pf');// 计算 HyperLogLog的基数的个数


// PUBLISH / SUBSCRIBE 发布与订阅
// 客户端中执行 SUBSCRIBE redisChat 即可接收到该频道的消息
$ps_res1 = $redis->publish('redisChat','Redis is a great caching technique');// 发布消息到 指定 频道，返回订阅的客户端的个数（订阅的客户端会接收到该消息）

//$count = 30;
//while($count > 0){// 执行 30 次
//    sleep(1);
//    $count --;
//    $ps_res1 = $redis->publish('redisChatTwo','hello world'.time());// 创建一个频道并发送消息到频道中 （客户端中执行 SUBSCRIBE redisChat 命令即可接收消息）
//}



// 事务：
$redis->del(['multi_1','multi_2']);

$redis->multi();// 开始事务
$multi_res = $redis->set('multi_1',123);
$multi_res = $redis->set('multi_2',222);

$redis->watch('multi_1');// 命令用于监视一个(或多个) key

for($i = 0; $i < 100; $i ++){
    $redis->incr('multi_1');
}
$redis->unwatch();// 取消监视

$redis->exec();// 执行事务
$redis->discard();// 取消事务（取消后 事务块里面的命令都不会执行）

$multi_res = $redis->get('multi_1');
$multi_res = $redis->get('multi_2');








echo "</pre>";

echo '<br/><br/><br/>';
echo 'END';
exit;



