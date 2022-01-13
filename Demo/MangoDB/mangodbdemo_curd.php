<?php
use DevelopModel\MongoHandle;
use DevelopModel\Mongo_db;


echo "<pre>";
ini_set('display_errors','On');
error_reporting(E_ALL);


function randomKeys($length = 18){
    $key     = '';
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    for($i = 0; $i < $length; $i++){
        $key .= $pattern{mt_rand(0, 35)};//生成php随机数
    }
    return $key;
}


$mongo_db = new Mongo_db();

//$res = $mongo_db->batch_insert('sites',[
//    ['uid' => randomKeys(), 'name'=>randomKeys(),'time' => time(),'url' => 'http://www.runoob.com','address' => '深圳市龙岗区'],
//    ['uid' => randomKeys(), 'name'=>randomKeys(),'time' => time(),'url' => 'http://www.runoob.com'],
//    ['uid' => randomKeys(), 'name'=>randomKeys(),'time' => time(),'url' => 'http://www.runoob.com']
//]);

$res = $mongo_db->switch_db('test');

$mongo_db->mongo_return = 'object';

$res = $mongo_db->where_gt('time',1585660181)
    ->offset(1)
    ->limit(4)
    ->get('sites');

print_r($res);


echo 'sss';exit;
