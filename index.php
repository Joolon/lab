<?php
/**
 * Created by JoLon.
 * 程序入口文件
 * User: JoLon
 * Date: 2016/6/24
 * Time: 18:04
 */

header("Content-type: text/html; charset=utf-8");

// 检测 PHP 环境是否符合要求
if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP > 5.3.0 !');
//检测是否已安装系统
if (file_exists("./Install/") && !file_exists("./Install/install.lock")) {
    if ($_SERVER['PHP_SELF'] != '/index.php') {// 当前文件相对于网站根目录的位置地址
        exit("请在域名根目录下安装,如:<br/> www.xxx.com/index.php 正确 <br/>  www.xxx.com/www/index.php 错误");
    }
    header('Location:/Install/index.php');
    exit();
}


// 系统初始化
define('BASE_PATH', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);


include_once 'autoload.php';// 自动加载类文件
include_once 'Common/function.php';


use \Db\Databases;
use \Libs\FileDirDeal;
use \Libs\TimeTool;


include_once 'Libs\TimeTool.class.php';

//print_pre(Databases::getInstance());exit;
//
//include_once 'Demo\Redis\redisdemo.php';
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
include_once 'Demo\MangoDB\mangodbdemo.php';








