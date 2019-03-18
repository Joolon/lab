<?php
include_once 'index.php';


use \Db\Databases;
use \Libs\FileDirDeal;
use \Libs\TimeTool;



$purFba = array(
    array(
        'id'            => '3',
        'order_id'      => '345',
        'is_clear'      => '2',
        'custom_number' => 'YB123455',
        'clear_time'    => '2019-01-31 12:12:00',

        'detail' => array(
            array(
                'detail_id'        => '1',
                'sku'           => 'DE-JYA00990',
                'pur_number'    => 'ABD000014',
                'amounts'       => '20',
                'price'         => '10.11',
                'declare_name'  => 'declare_name',
                'declare_unit'  => 'declare_unit',
                'declare'       => 'declare'
            ),
            array(
                'detail_id'        => '2',
                'sku'           => 'DE-JYA00990',
                'pur_number'    => 'ABD000014',
                'amounts'       => '30',
                'price'         => '10.11',
                'declare_name'  => 'declare_name',
                'declare_unit'  => 'declare_unit',
                'declare'       => 'declare'
            ),
        )
    ),

);


$purFba = array(
    array(
        'id'              => '你那边的ID（用来返回给你是否执行成功）',
        'pur_number'      => 'ABD000001',
        'sku'             => 'DC00001',
        'status'          => '是否完结 1:未完结,2:已完结',
        'stock'           => '12',
        'warehouse_code'     => 'AFN',
    ),
    array(
        'id'              => '你那边的ID（用来返回给你是否执行成功）',
        'pur_number'      => 'ABD000001',
        'sku'             => 'DC00002',
        'status'          => '是否完结 1:未完结,2:已完结',
        'stock'           => '13',
        'warehouse_code'     => 'AFN',
    )
);

$data = ['data'=> json_encode($purFba,JSON_UNESCAPED_UNICODE)];


print_r($data);

echo "\n\n";
print_r($purFba);

exit;

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






