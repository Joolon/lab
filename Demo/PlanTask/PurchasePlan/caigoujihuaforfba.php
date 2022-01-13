<?php
@session_start();
//error_reporting(0);

$user = $truename = $_SESSION['user'] = 'otw';

include_once '../taskbase.php';
include_once 'common.php';
include_once BASE_PATH.'class/StockUpDeal.class.php';
$stockUpDeal = new StockUpDeal();


$jihuaid	= date('Y-m-d H');


/******* AMAZON-FBA 采购计划 START  *****/
subOperation("caigoujihua".date('Y-m'),"AMAZON-FBA 采购计划开始时间：[".date("Y-m-d H:i:s")."]");
$stockUpDeal->purchasePlan($jihuaid);
subOperation("caigoujihua".date('Y-m'),"AMAZON-FBA 采购计划结束时间：[".date("Y-m-d H:i:s")."]");

echo 'AMAZON-FBA 采购计划 运行结束<br/>';
/******* AMAZON-FBA 采购计划 END  *****/


?>



