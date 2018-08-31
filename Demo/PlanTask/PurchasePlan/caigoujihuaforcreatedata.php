<?php

// 更新 SKU 占用数、待分配库存数、预订数
include_once BASE_PATH.'class/InventoryReportHelp.class.php';

subOperation("caigoujihua".date('Y-m'),'数据生成:库存统计START');
InventoryReportHelp::createSkuInventoryDatas(1,300);// 1.强制更新，有效时间 300秒
subOperation("caigoujihua".date('Y-m'),'数据生成:库存统计END');

// 根据付款时间区间统计SKU的销量
function calculateSaleCountByTime($start_time,$end_time){
    global $dbcon;

    $saleCount = "SELECT UPPER(trim(b.sku)) as sku, SUM( b.ebay_amount ) AS cc
            FROM ebay_order a
            LEFT JOIN ebay_orderdetail b ON ( a.ebay_ordersn = b.ebay_ordersn ) 
            WHERE a.ebay_combine !=  '1' AND a.ebay_ordertype !=  'Y-W' AND a.ebay_status !=236
            AND b.ebay_amount <50 AND (a.ebay_paidtime>'$start_time' AND a.ebay_paidtime<'$end_time')
            AND a.ebay_warehouse =  '32'
            GROUP BY b.sku";
    $saleCount = $dbcon->query($saleCount);
    $saleCount = $dbcon->getResultArray($saleCount);

    if($saleCount) $saleCount = get_array_column($saleCount,'cc','sku');
    return $saleCount;
}

// SKU 7/15/30天的销量 缓存数据到表中
function calculateSaleCountFor7And15And30(){
    global $dbcon;

    // 获取 7天/15天/30天的销量
    $start1			    = date('Y-m-d').'23:59:59';
    $end_time           = strtotime($start1);
    $date_7				= date('Y-m-d',strtotime("$start1 -7 days")).'00:00:00';
    $date_15		    = date('Y-m-d',strtotime("$start1 -15 days")).'00:00:00';
    $date_30		    = date('Y-m-d',strtotime("$start1 -30 days")).'00:00:00';
    $time_7             = strtotime($date_7);
    $time_15            = strtotime($date_15);
    $time_30            = strtotime($date_30);

    // 15天销量分成 7+8查询，30天销量分成 15+15查询
    echo '7天时间段：'.$time_7.'~'.$end_time;echo '<br/>';
    echo '15天时间段：'.$time_15.'~'.$time_7;echo '<br/>';
    echo '30天时间段：'.$time_30.'~'.$time_15;echo '<br/>';

    $saleCount7     = calculateSaleCountByTime($time_7,$end_time);
    $saleCount15    = calculateSaleCountByTime($time_15,$time_7);// 实际上查询的是 8 天
    $saleCount30    = calculateSaleCountByTime($time_30,$time_15);// 实际上查询的是 15 天
//    print_r($saleCount7);exit;

    // 获取所有SKU 列表
    $sku_all = array_merge(array_keys($saleCount7),array_keys($saleCount15),array_keys($saleCount30));
    $sku_all = array_unique($sku_all);
    

    $dbcon->execute("TRUNCATE TABLE sku_salecount7and15and30_cache");// 清空旧的数据

    $time      = time();
    $sqlInsert = "INSERT sku_salecount7and15and30_cache(goods_sn,sale_7,sale_15,sale_30) VALUES ('XXXX-".$time."','-1','-1','-1') ";
    $dbcon->execute($sqlInsert);

    // 保存数据
    $sqlInsert      = "INSERT sku_salecount7and15and30_cache(goods_sn,sale_7,sale_15,sale_30) VALUES ";
    $sqlInsertSub   = '';
    foreach($sku_all as $key => $goods_sn){
        $sale_7     = isset($saleCount7[$goods_sn])?$saleCount7[$goods_sn]:0;// 7天销量
        $sale_15    = isset($saleCount15[$goods_sn])?$saleCount15[$goods_sn]:0;// 8天销量
        $sale_15    = $sale_15 + $sale_7;// 7+8天销量
        $sale_30    = isset($saleCount30[$goods_sn])?$saleCount30[$goods_sn]:0;// 15天销量
        $sale_30    = $sale_30 + $sale_15;// 15+15天销量

        $goods_sn   = mysql_escape_string($goods_sn);// 特殊字符

        $sqlInsertSub .= "('$goods_sn','$sale_7','$sale_15','$sale_30'),";

        if($key % 100 == 0){// 每100条记录插入一次
            $sqlInsertSub = trim($sqlInsertSub,',');
            $sqlInsertNow = $sqlInsert.$sqlInsertSub;
            echo $sqlInsertNow;echo '<br/><br/>';
            $res = $dbcon->execute($sqlInsertNow);
            $sqlInsertSub = '';
            if(empty($res)){
                subOperation("caigoujihua".date('Y-m'),'数据生成:7/15/30销量统计失败：'.$sqlInsertNow);
            }
        }
    }
    if($sqlInsertSub){
        $sqlInsertSub = trim($sqlInsertSub,',');
        $sqlInsertNow = $sqlInsert.$sqlInsertSub;
        echo $sqlInsertNow;echo '<br/><br/>';
        $res = $dbcon->execute($sqlInsertNow);
        if(empty($res)){
            subOperation("caigoujihua".date('Y-m'),'数据生成:7/15/30销量统计失败：'.$sqlInsertNow);
        }
    }

    return true;
}

subOperation("caigoujihua".date('Y-m'),'数据生成:7/15/30销量统计START');
calculateSaleCountFor7And15And30();
subOperation("caigoujihua".date('Y-m'),'数据生成:7/15/30销量统计END');







