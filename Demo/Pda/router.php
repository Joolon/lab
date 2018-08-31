<?php
//error_reporting(0);

/**
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */

$params = $_SERVER['REQUEST_URI'] ;
//print_r($params);exit;

$c = isset($_GET['c'])?trim($_GET['c']):'Index';
$m = isset($_GET['m'])?trim($_GET['m']):'index';



$c = $c.'Action';
$className = ucfirst($c);// 单词首字母大写

//echo $className;exit;

include_once PDA_BASE_PATH.'loginfilter.php';// 登录验证过滤器

// 自动包含类文件，调用指定方法
try{
    $currentObj = new $className();

    $params = urlStrToArray($params);
    call_user_func_array(array($currentObj, $m), array($params));

}catch (CustomException $e){
    api_return(api_error($e->errorMessage()));
    exit;

}catch (Exception $e){
    api_return(api_error($e->getMessage()));
    exit;

}

