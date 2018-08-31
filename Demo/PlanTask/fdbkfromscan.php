<?php

include 'taskbase.php';
include_once BASE_PATH."include/config.php";


//计算评价时间与发货时间差
planStart(2);
$startime = time();
subOperation("caigoujihua".date('Y-m'),"计算评价时间与发货时间差开始时间：[".date("Y-m-d H:i:s")."]");

$sql0 = "delete from ebay_fdbkfromscan";
$dbcon->execute($sql0);

$sql1 	= "INSERT INTO ebay_fdbkfromscan (scantime,fdbkdaysfromscan,orderqty,ebay_countryname,ebay_carrier,system_shippingcarriername) ";
$day0	= date('Y-m-d H:i:s');	//执行当前时间
$day30	= strtotime("$day0 -90 days");
$day30	= date('Y-m-d',$day30);
$sql2	= "select FROM_UNIXTIME(scantime,'%Y-%m-%d'),avg((a.feedbacktime-c.scantime)/86400) as fdbkdaysfromscan,count(1) as orderqty,c.ebay_countryname,c.ebay_carrier,c.system_shippingcarriername 
		from ebay_feedback a inner join ebay_orderdetail b on (a.transactionid=b.ebay_tid and a.transactionid<>0 and a.commenttype='Positive') 
		inner join ebay_order c on (b.ebay_ordersn=c.ebay_ordersn and c.ebay_ordertype='EBAY订单' and c.scantime>0)  
		where FROM_UNIXTIME(scantime,'%Y-%m-%d')>='$day30' and (a.feedbacktime-c.scantime)/86400>5 and (a.feedbacktime-c.scantime)/86400<30 
		and c.ebay_countryname in ('United Kingdom','Australia','United States','Russian Federation','Israel','Canada',
		'Brazil','Norway','Hungary','Sweden','Croatia',' Republic of','Argentina','Spain','Greece','Portugal','Denmark',
		'Ireland','Czech Republic','Netherlands','Latvia','Russia','Finland','Malta','Slovakia','Lithuania','Ukraine','Thailand',
		'Sri Lanka','New Zealand','Cyprus') 
		group by FROM_UNIXTIME(scantime,'%Y-%m-%d'),c.ebay_countryname,c.ebay_carrier,c.system_shippingcarriername";
$dbcon->execute($sql1.$sql2);

$usetime	= (time()-$startime)/60;
$strtxt		= "计算评价时间与发货时间差结束时间：[".date("Y-m-d H:i:s")."]，用时（分）：".$usetime;
subOperation("caigoujihua".date('Y-m'),$strtxt);

planEnd(2,$startime);
