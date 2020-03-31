<?php
/**
 * Created by JoLon.
 * 程序入口文件
 * User: JoLon
 * Date: 2016/6/24
 * Time: 18:04
 */

header("Content-type: text/html; charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');

// 检测 PHP 环境是否符合要求
if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP > 5.3.0 !');

// 系统初始化
define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(__FILE__).DS);

//include_once BASE_PATH.'Install'.DS.'check_install.php';

include_once BASE_PATH.'autoload.php';// 自动加载类文件
include_once BASE_PATH.'Common/function.php';


include_once BASE_PATH.'Demo/MangoDB/mangodbdemo.php';
exit;
//include_once BASE_PATH.'Demo/Redis/test_seckill.php';



