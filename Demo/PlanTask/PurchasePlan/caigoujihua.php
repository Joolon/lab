<?php
@session_start();
error_reporting(0);

include_once 'E:\TOOLS\htdocs\PlanTask/taskbase.php';
include_once 'common.php';



$startime	= time();
planStart(1);
subOperation("caigoujihua".date('Y-m'),"采购计划生产开始时间：[".date("Y-m-d H:i:s")."],");


$jihuaid	= date('Y-m-d H');

subOperation("caigoujihua".date('Y-m'),'数据生成开始');
/**  只运行一次 END */
include_once 'caigoujihuaforcreatedata.php';// 更新 SKU 占用数、待分配库存数、预订数
/**  只运行一次 END */
subOperation("caigoujihua".date('Y-m'),'数据生成结束');


$data_index = 'data0';
subOperation("caigoujihua".date('Y-m'),'采购计划统计开始（'.$data_index.'）');

// 按LIMIT 查询条件划分为多任务处理
if(date('H') == 0){// 0点运行时每次10W个，其他每次6W个
    $offset         =     0;
    $total_count    = 40000;
}else{
    $offset         =     0;
    $total_count    = 30000;
}

PurchasePlan::startPlan($jihuaid,32,1);
$resData = PurchasePlan::runPlan($jihuaid,$offset,$total_count);
PurchasePlan::endPlan(32,$resData,1);

// 更新统计数据
include_once 'caigoujihuaforupdatestatistics.php';

subOperation("caigoujihua".date('Y-m'),'采购计划统计结束（'.$data_index.'）');




