<?php
include_once 'index.php';

/*
PHP7 三元运算符：缩写形式  前面表达式为真 则返回表达式的结果，否则返回 设定值

常量数组：defind 定义常量的值为数组
命名空间引入简写：同一命名空间下文件引入 可以批量引入
组合运算符（<=>）：用于整型、浮点型数值比较大小。前者<后者为-1，等于后者为0，大于后者为1
拓展：移除了 MsSQL  MySQL 等拓展
加入：
1、空合并运算符（??）优化 isset()方法（使用 ?? 即使变量未定义也不会报错，使用 ?: 如果变量未定义则报致命错误）




运行速度更快：
1、变量存储字节变小，减少内存占用，加快变量操作速度
2、数组结构优化，数组元素和hash映射表分配在同一个内存中，提升了CPU的缓存命中率
3、改善函数调用机制，优化参数传递的环节，减少了一些指令，提高执行效率




*/



use \Db\Databases;
use \Libs\FileDirDeal;
use \Libs\TimeTool;


include_once 'Libs/TimeTool.class.php';

//print_pre(Databases::getInstance());exit;
//
include_once 'Demo/Redis/redisdemo.php';exit;
//$list = FileDirDeal::readAllFile('d:/phpStudy/WWW/lab',array('gif','php'),true,true,true);
//print_pre($list);
//exit;
//
//$results = Databases::getInstance()
//    ->select('account','id')
//    ->from('xy_user')
//    ->where('account', '=', 'admin')
//    ->execue('yeagleplan')
////    ->compile();
//    ->current();
//
//print_r($results);exit;
//include_once 'Demo\Memcache\memcachedemo.php';
//include_once 'Demo\MangoDB\mangodbdemo.php';


//include_once 'Libs\CurlRequest.class.php';
//$http_request = new \Libs\CurlRequest();
//$time_stamp = time();
//$token = $http_request->getToken($time_stamp);
//$response = $http_request->curlPost('http://localhost/lab/index2.php',['user'=>123,'abd','faf','fas'],$token);
//print_r($response);exit;






