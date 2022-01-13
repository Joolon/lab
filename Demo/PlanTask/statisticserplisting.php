<?php
include 'taskbase.php';
include_once BASE_PATH."include/config.php";
include_once BASE_PATH.'include/dbconnecterp_slave.php';
include_once BASE_PATH."include/tools/arrayfunction.php";
include_once BASE_PATH.'class/InventoryReportHelp.class.php';
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);
$dbcon      = new DBClass();

$startime	= time();
$dbcon->execute("update system_task set taskstatus=1,taskstarttime=".time().",RunTime=0 where ID=81 ");
$dbcon->execute('truncate table sku_listing_statistics ');// 清空历史数据

// 统计SKU的总个数，分页查询SKU数据
$sql_skucount = "SELECT count(1) as num 
            FROM ebay_goods AS e_g
            LEFT JOIN ebay_onhandle AS e_oh ON (e_oh.goods_sn=e_g.goods_sn AND  e_oh.store_id=32)
            WHERE 1  ";
$sql_skucount = $dbcon->query($sql_skucount);
$sql_skucount = $dbcon->getResultArray($sql_skucount);
$count = $sql_skucount[0]['num'];
$ebay_accounts = "SELECT ebay_account,charge,left(deptname,6) as deptname FROM ebay_account AS e_a 
            LEFT JOIN ebay_user AS e_u ON e_u.username=e_a.charge 
            WHERE e_a.ebay_type='EBAY订单' AND e_a.active=0  ";
$ebay_accounts = $dbcon->query($ebay_accounts);
$ebay_accounts = $dbcon->getResultArray($ebay_accounts);
$ebay_accounts = get_array_column($ebay_accounts,'deptname','ebay_account');
$c = 0 ;
// 分批次查询，每次查询1000条记录
for($i = 0;$i < $count;$i += 1000){
	$c ++ ;
	$start_limit = ($c-1) * 1000;
    $sql_sku = "SELECT e_g.goods_sn,e_g.goods_name,e_g.goods_cost,e_g.goods_location,e_g.cguser,e_g.salesuser,e_g.qty7,e_g.qty15,e_g.qty30,
                e_g.isuse,e_g.goods_category,FROM_UNIXTIME( e_g.lastruku ) AS create_date,e_oh.goods_count
            FROM ebay_goods AS e_g
            LEFT JOIN ebay_onhandle AS e_oh ON (e_oh.goods_sn=e_g.goods_sn AND  e_oh.store_id=32)
            WHERE 1 LIMIT $start_limit,1000 ";
    $sql_sku = $dbcon->query($sql_sku);
    $sql_sku = $dbcon->getResultArray($sql_sku);
    $skus = get_array_column($sql_sku,'goods_sn');
    $dbcon->close();
    $dbconErp = new DBClasserpslave();
    $listing = InventoryReportHelp::getListingBySku($skus,$ebay_accounts);
    $dbconErp->close();
    $dbcon = new DBClass();
    $nowCount = count($skus);
    $sql_insert = "INSERT INTO sku_listing_statistics(goods_sn,ebay,ebay2,ali,wish) VALUES ";
    $sql_insert_sub = '';
    foreach($skus as $sku){
        //$ebay   = isset($listing['ebay']['ebay1部'][$sku])?$listing['ebay']['ebay1部'][$sku]:0;
        $ebay_1 = isset($listing['ebay']['ebay1组'][$sku])?$listing['ebay']['ebay1组'][$sku]:0;
        $ebay_2 = isset($listing['ebay']['ebay2组'][$sku])?$listing['ebay']['ebay2组'][$sku]:0;
        $ebay_3 = isset($listing['ebay']['ebay3组'][$sku])?$listing['ebay']['ebay3组'][$sku]:0;
        $ebay_4 = isset($listing['ebay']['ebay4组'][$sku])?$listing['ebay']['ebay4组'][$sku]:0;
        $ebay_5 = isset($listing['ebay']['ebay5组'][$sku])?$listing['ebay']['ebay5组'][$sku]:0;
        $ebay = $ebay_1+$ebay_2+$ebay_3+$ebay_4+$ebay_5;
        $ebay2 = isset($listing['ebay']['ebay2部'][$sku])?$listing['ebay']['ebay2部'][$sku]:0;
        $ali = isset($listing['ali'][$sku])?$listing['ali'][$sku]:0;
        $wish = isset($listing['wish'][$sku])?$listing['wish'][$sku]:0;
        $sql_insert_sub .=" ('$sku','$ebay','$ebay2','$ali','$wish'),";// 批量插入

        // 更新Listing记录到产品资料
        $dbcon->execute("update ebay_goods set ebayqty='".($ebay+$ebay2)."',aliqty='$ali',wishqty='$wish' where goods_sn='$sku'  limit 1 ");
    }
    $sql_insert_sub = rtrim($sql_insert_sub,',');
    $dbcon->execute($sql_insert.$sql_insert_sub);

}

$dbcon = new DBClass();
$usetime = (time()-$startime)/60;
$detail = '统计完成：统计总数：'.$count;
$dbcon->execute("update system_task set Runtime=".time().",UseTime=".$usetime.",taskstatus=0,TaskDetail='$detail' where ID=81 ");
echo '<font color="green">'.$detail.'</font>';
exit;

