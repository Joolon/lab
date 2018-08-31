<?php

/**
 * 保存采购运行后统计的数据
 */

include_once BASE_PATH.'class/FileCache.php';


// 当前统计的数据
$now_data = $resData;
$now_data['date'] = date('Y-m-d H:i:s');// 统计时间

// 保存数据
$statisticsData = FileCache::getCacheFileContent('caigoujihuaforupdatestatistics',3600*3);// 3600秒
$statisticsData[$data_index] = $now_data;
FileCache::saveCacheFile('caigoujihuaforupdatestatistics',$statisticsData);

// 只有3个任务都运行完毕才更新 汇总数据
if(count($statisticsData) == 3){
    // 统计多个任务中数据的汇总值
    $all_totalneedsku = $all_totaloossku = $all_totalneedamount = 0;
    foreach($statisticsData as $value){
        $now_totalneedsku       = $value['totalneedsku'];
        $now_totaloossku        = $value['totaloossku'];
        $now_totalneedamount    = $value['totalneedamount'];

        $all_totalneedsku       += $now_totalneedsku;
        $all_totaloossku        += $now_totaloossku;
        $all_totalneedamount    += $now_totalneedamount;
    }


    $uptconfig = "update ebay_config 
        set caigoujhdata='"."库存预警SKU数：".$all_totalneedsku."，库存缺货SKU数：<font color=red>"
        .$all_totaloossku."</font>，统计时间：".date('Y-m-d H:i:s')."',
    caigoujhskuamount=$all_totalneedamount ";
    subOperation("caigoujihua".date('Y-m'),$uptconfig);
    //$dbcon->execute($uptconfig);


    planEnd(1,$startime);
    subOperation("caigoujihua".date('Y-m'),'采购计划数据汇总统计完成（'.$data_index.'）');
}



