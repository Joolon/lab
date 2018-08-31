<?php
@session_start();
error_reporting(0);

include_once 'E:\TOOLS\htdocs\PlanTask/taskbase.php';
include_once 'common.php';



$startime	= time();
$jihuaid	= date('Y-m-d H');

$data_index = 'data1';
subOperation("caigoujihua".date('Y-m'),'采购计划统计开始（'.$data_index.'）');

if(date('H') == 0){
    $offset         =  40000;
    $total_count    = 160000;
}else{
    $offset         =  30000;
    $total_count    = 100000;
}
PurchasePlan::startPlan($jihuaid,32,2);
$resData = PurchasePlan::runPlan($jihuaid,$offset,$total_count);
PurchasePlan::endPlan(32,$resData,2);

// 更新统计数据
include_once 'caigoujihuaforupdatestatistics.php';

subOperation("caigoujihua".date('Y-m'),'采购计划统计结束（'.$data_index.'）');


