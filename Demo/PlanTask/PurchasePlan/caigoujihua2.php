<?php
@session_start();
error_reporting(0);

include_once 'E:\TOOLS\htdocs\PlanTask/taskbase.php';
include_once 'common.php';



$startime	= time();
$jihuaid	= date('Y-m-d H');

$data_index = 'data2';
subOperation("caigoujihua".date('Y-m'),'采购计划统计开始（'.$data_index.'）');

if(date('H') == 0){
    $offset         = 160000;
    $total_count    = 350000;
}else{
    $offset         = 100000;
    $total_count    = 250000;
}
PurchasePlan::startPlan($jihuaid,32,3);
$resData = PurchasePlan::runPlan($jihuaid,$offset,$total_count);
PurchasePlan::endPlan(32,$resData,3);

// 更新统计数据
include_once 'caigoujihuaforupdatestatistics.php';

subOperation("caigoujihua".date('Y-m'),'采购计划统计结束（'.$data_index.'）');



