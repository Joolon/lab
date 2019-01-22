<?php
include_once 'index.php';


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






