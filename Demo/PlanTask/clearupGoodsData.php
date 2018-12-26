<?php
/**
 * Created by JoLon.
 * User: Jolon
 * Date: 2017/11/21
 * Time: 11:19
 */

include 'taskbase.php';
include_once BASE_PATH."include/config.php";
include_once BASE_PATH."include/dbconnecterp.php";
include_once BASE_PATH."include/functions.php";
include_once BASE_PATH."class/HhStock.class.php";
include_once BASE_PATH."class/Goods.class.php";
include_once BASE_PATH.'class/GoodsPurchaseHelp.class.php';

// 每天凌晨4点执行

// 1.每天执行一次，将采购员为空的设置成王春娇
planStart(83);
$startime_1     = time();
$sql0 = "update ebay_goods set cguser='王春娇' where isnull(cguser) or cguser='' ";
$dbcon->execute($sql0);
planEnd(83,$startime_1);


// 2.新开发状态下开发时间超过120天没有出库记录的产品设置状态为下线
planStart(84);
$startime_2     = time();
$longtime = date('Y-m-d H:i:s');
$longtime = strtotime("$longtime -120 days");

// 5.新开发状态
$offlineGoodsList = "SELECT goods_sn FROM ebay_goods WHERE isuse=5 AND (lastexport='' OR isnull(lastexport)) AND addtim<=".$longtime;
$offlineGoodsList = $dbcon->query($offlineGoodsList);
$offlineGoodsList = $dbcon->getResultArray($offlineGoodsList);
//print_r($offlineGoodsList);exit;

$warehouse_id = 32;
$now_time = time();

foreach($offlineGoodsList as $goods){
    $goods_sn           = $goods['goods_sn'];

    $goodsStockCount    = HhStock::getAllStoreTotalStock($goods_sn);// 获取库存数量
    $bookedCount        = GoodsPurchaseHelp::getStockBookUsed($goods_sn);
    // 库存数为0的设置为下线
    if((empty($goodsStockCount) OR $goodsStockCount <= 0) AND (empty($bookedCount) OR $bookedCount <= 0)){
        // 1.下线状态
        $sqlUp = "UPDATE ebay_goods SET isuse=1,offlinetime=$now_time,isusechangetime=$now_time WHERE goods_sn='$goods_sn' LIMIT 1";
        if($dbcon->update($sqlUp)){
            Goods::addOperationLog($goods_sn,40,"修改状态从[新开发]到[下线],文件位置[PlanTask/clearupGoodsData.php]");
        }
    }
}
planEnd(84,$startime_2);


// 3.每天执行一次，统计库存金额
planStart(85);
$startime_3     = time();
$uptSystemTask  = "update system_task set taskstatus=1,taskstarttime=" . time() . ",RunTime=0 where ID=85 ";
$dbcon->execute($uptSystemTask);
$uptstockamount = "insert into ebay_goodsamount (rq,stockamount)
    select FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_DATE),'%Y-%m-%d') as rq,sum(b.goods_cost*a.goodscount) as stockamount
    from ( select goods_sn,sum(goods_count) as goodscount from ebay_onhandle group by goods_sn ) a
    left join ebay_goods b on (a.goods_sn=b.goods_sn)";
$dbcon->execute($uptstockamount);
planEnd(85,$startime_3);


// 4.计算缺货情况
planStart(25);
$startime_4     = time();
$sql = "select ebay_id from ebay_order where ebay_combine !=1 and (ebay_status=230 or ebay_status=232) and usestore=0 ";
$sql = $dbcon->execute($sql);
$sql = $dbcon->getResultArray($sql);
subOperation("caigoujihua".date('Y-m'),'计算缺货情况,总订单数'.count($sql));
subOperation("quehuotj".date('Y-m'),'计算缺货情况,总订单数'.count($sql));

// 删除90天之前的记录
$day0   = date('Y-m-d H:i:s');	//执行当前时间
$day1   = strtotime("$day0 -90 days");
$dbcon->execute('delete from ebay_quehuo where createtime < '.$day1);
for($i=0;$i<count($sql);$i++){	//主记录表
    $ebay_id = $sql[$i]['ebay_id'];

    $ss = "select a.ebay_id,a.ebay_paidtime,c.goods_count,b.sku,d.isuse,b.ebay_amount,d.cguser
        from ebay_order a
        left join ebay_orderdetail b on (a.ebay_ordersn=b.ebay_ordersn)
        left join ebay_onhandle c on (b.sku=c.goods_sn)
        left join ebay_goods d on (c.goods_sn=d.goods_sn) where a.ebay_id=$ebay_id";
    //echo '<br>'.$ss;
    $ss					= $dbcon->execute($ss);
    $ss					= $dbcon->getResultArray($ss);
    for($j=0;$j<count($ss);$j++){
        $ebay_paidtime	= $ss[$j]['ebay_paidtime'];
        $goods_count	= $ss[$j]['goods_count'];
        $sku	        = $ss[$j]['sku'];
        $isuse	        = $ss[$j]['isuse'];
        $ebay_amount	= $ss[$j]['ebay_amount'];
        $cguser	        = $ss[$j]['cguser'];
        $confirmdate    = fnGetOrderConfirmDate($ebay_id);
        if($confirmdate=='') $confirmdate   = $ss[$j]['ebay_paidtime'];	//自动确认或拆单订单没有确认时间byzhuwf2014-11-22
        $dddays         = round(($mctime-$confirmdate)/86400,1);
        $stockused	    = stockused($sku,32);
        $stockbookused	= stockbookused($sku,32);
        //缺货超过一天
        if(($goods_count-$stockused-$ebay_amount) < 0 and $dddays > 1){
            $strtxt= '\r\n'.$i."/".count($sql).' ---><font color=red>'.'ebay_id='.$ebay_id.' cguser='.$cguser.' ebay_paidtime='.date('Y-m-d H:i:s',$ebay_paidtime).' confirmdate='.date('Y-m-d H:i:s',$confirmdate).' dddays='.$dddays.' sku='.$sku.' goods_count='.$goods_count.' ebay_amount='.$ebay_amount.' stockused='.$stockused.' stockbookused='.$stockbookused.'</font>';
            echo $strtxt;
            $intsql="insert into ebay_quehuo (ebay_id,cguser,ebay_paidtime,confirmdate,dddays,sku,isuse,goods_count,ebay_amount,stockused,stockbookused,createtime)
                     values ($ebay_id,'$cguser',$ebay_paidtime,'$confirmdate',$dddays,'$sku',$isuse,$goods_count,$ebay_amount,$stockused,$stockbookused,$mctime)";
            echo $intsql;
            $dbcon->execute($intsql);
        }else{
            echo '\r\n'.$i."/".count($sql).' ---><font color=green>'.'ebay_id='.$ebay_id.' cguser='.$cguser.' ebay_paidtime='.date('Y-m-d H:i:s',$ebay_paidtime).' confirmdate='.date('Y-m-d H:i:s',$confirmdate).' dddays='.$dddays.' sku='.$sku.' goods_count='.$goods_count.' ebay_amount='.$ebay_amount.' stockused='.$stockused.' stockbookused='.$stockbookused.'</font>';
        }
    }
}
echo 'Use Time:' . (time() - $startime_4);
planEnd(25,$startime_4);


// 6.SKU，LISTING分析数据预处理
planStart(26);
$startime_6    = time();

$day0   = date('Y-m-d H:i:s');    //执行当前时间
$day1   = date('Y-m-d', strtotime("$day0 -1 days"));
$sql    = "select ebay_id,ebay_ordersn,ebay_ordertype,ebay_currency,ebay_total,ebay_carrier,orderweight,orderweight2,aa_postage,aa_profit
          from ebay_order where ebay_combine !=1 and ebay_status=2 and aa_profit<>0 and FROM_UNIXTIME(scantime,'%Y-%m-%d') ='$day1' ";
$sql    = $dbcon->execute($sql);
$sql    = $dbcon->getResultArray($sql);
for ($i = 0; $i < count($sql); $i++) {    //主记录表
    $ebay_id        = $sql[$i]['ebay_id'];
    $ebay_ordersn   = $sql[$i]['ebay_ordersn'];
    $ebay_currency  = $sql[$i]['ebay_currency'];
    $ebay_total     = $sql[$i]['ebay_total'];
    $ebay_carrier   = $sql[$i]['ebay_carrier'];
    $orderweight    = $sql[$i]['orderweight'];
    $orderweight2   = $sql[$i]['orderweight2'];
    $aa_postage     = $sql[$i]['aa_postage'];
    $aa_profit      = $sql[$i]['aa_profit'];
    $totalprofit    = 0;

    $nn = "select * from ebay_currency where currency ='$ebay_currency' and user ='$user' ";
    $nn = $dbcon->execute($nn);
    $nn = $dbcon->getResultArray($nn);
    $rates = $nn[0]['rates'] ? $nn[0]['rates'] : 1;
    echo '<br>汇率：' . $rates;

    $ss = "select sum(b.goods_weight*a.ebay_amount) as orderweightfromgoodstable,sum(a.ebay_itemprice*a.ebay_amount+a.shipingfee) as totalamountfromdorder
          from ebay_orderdetail a
          left join ebay_goods b on (a.sku=b.goods_sn)
          where a.ebay_ordersn='$ebay_ordersn'";
    $ss = $dbcon->execute($ss);
    $ss = $dbcon->getResultArray($ss);
    $orderweightfromgoodstable  = $ss[0]['orderweightfromgoodstable'];
    $totalamountfromdorder      = $ss[0]['totalamountfromdorder'];

    $ss = "select a.ebay_id,a.sku,b.goods_weight,b.goods_cost,c.price,a.ebay_amount,a.ebay_itemprice,a.shipingfee,
            a.ebay_site,a.FinalValueFee,a.FeeOrCreditAmount
        from ebay_orderdetail a
        left join ebay_goods b on (a.sku=b.goods_sn)
        left join ebay_packingmaterial c on (b.ebay_packingmaterial=c.model)
        where a.ebay_ordersn='$ebay_ordersn'";
    echo '<br>' . $ss;
    $ss = $dbcon->execute($ss);
    $ss = $dbcon->getResultArray($ss);
    for ($j = 0; $j < count($ss); $j++) {
        $ebaydid            = $ss[$j]['ebay_id'];
        $sku                = $ss[$j]['sku'];
        $goods_weight       = $ss[$j]['goods_weight'];
        $goods_cost         = $ss[$j]['goods_cost'];
        $packprice          = $ss[$j]['price'] ? $ss[$j]['price'] : 0.55;
        $ebay_amount        = $ss[$j]['ebay_amount'];
        $ebay_itemprice     = $ss[$j]['ebay_itemprice'];
        $shipingfee         = $ss[$j]['shipingfee'];
        $ebay_site          = $ss[$j]['ebay_site'];
        $FinalValueFee      = $ss[$j]['FinalValueFee'];
        $FeeOrCreditAmount  = $ss[$j]['FeeOrCreditAmount'];

        //SHIPCOST,PP费用无法调取，直接按售价均摊,EBAY,SMT等各订单类型需要区分费用计算规则
        $subweight      = $goods_weight * $ebay_amount;
        $subpostage     = $subweight / $orderweightfromgoodstable * $aa_postage;
        $subppfees      = ($ebay_itemprice * $ebay_amount + $shipingfee) / $totalamountfromdorder * $FeeOrCreditAmount;
        $subprofit      = ($ebay_itemprice * $ebay_amount + $shipingfee - $FinalValueFee - $subppfees) * $rates - ($goods_cost + $packprice) * $ebay_amount - $subpostage;
        $totalprofit    = $totalprofit + $subprofit;
        $uptdaapostage  = "update ebay_orderdetail set aa_postage='$subpostage',aa_profit='$subprofit' where ebay_id='$ebaydid'";
        subOperation("skulistingfx" . date('Y-m'), $uptdaapostage);
        $dbcon->execute($uptdaapostage);
    }
    echo '<br>totalprofit=' . $totalprofit;
    if ($totalprofit / $aa_profit >= 1.1 || $totalprofit / $aa_profit <= 0.9) {
        echo '<br>totalprofit==' . $totalprofit;
        subOperation("skulistingfx" . date('Y-m'), '相差10%：' . $ebay_id . '-->totalprofit:' . $totalprofit . ',aa_profit:' . $aa_profit);
    }
}

echo '<br>Use Time:'.(time()-$startime_6);
planEnd(26,$startime_6);


// 7.计算SMT活动销售情况by zhuwf 20140711
planStart(27);
$startime_7    = time();
fnsmthuodong();
planEnd(27,$startime_7);


// 8.删除无效库存记录,设置产品等级by zhuwf 20140717
planStart(86);
$startime_8    = time();
fnGoodsDelNouseSetGrade();
planEnd(86,$startime_8);


// 10.V2产品的最后销售时间更新到erp系统
/****** S: wish  ali的销售情况，获取产品的销售时间插入到erp ******/
planStart(88);
$startime_10    = time();

$paidStart  = strtotime('-1 day 00:00:00');
$paidEnd    = strtotime('-1 day 23:59:59');
//wish
$wishSql = "select a.ebay_itemid,b.ebay_paidtime from ebay_orderdetail a inner join ebay_order b on a.ebay_ordersn=b.ebay_ordersn where b.ebay_ordertype='WISH' and b.ebay_paidtime>'$paidStart' and b.ebay_paidtime<'$paidEnd'";
$wishSql = $dbcon->execute($wishSql);
$wishSql = $dbcon->getResultArray($wishSql);
$wishData = array();
foreach($wishSql as $wishS){
    $wishData[$wishS['ebay_itemid']] = $wishS['ebay_paidtime'];
}
//ali
$aliSql = "select a.ebay_itemid,b.ebay_paidtime from ebay_orderdetail a inner join ebay_order b on a.ebay_ordersn=b.ebay_ordersn where b.ebay_ordertype='ALI-EXPRESS' and b.ebay_paidtime>'$paidStart' and b.ebay_paidtime<'$paidEnd'";
$aliSql = $dbcon->execute($aliSql);
$aliSql = $dbcon->getResultArray($aliSql);
$aliData = array();
foreach($aliSql as $aliS){
    $aliData[$aliS['ebay_itemid']] = $aliS['ebay_paidtime'];
}

unset($wishSql);
unset($aliSql);
$dbcon->close();


$dbconErp	= new DBClasserp();
//wish
foreach($wishData as $key=>$value){
    $dbconErp->execute('update frode_wish_items set lastpurchasetime='.$value.' where itemid="'.$key.'"');
    echo "update ".$key." 最后购买时间".$value.' success<br>';
}
//ali
foreach($aliData as $key=>$value){
    $dbconErp->execute('update frode_ali_items set lastpurchasetime='.$value.' where itemid="'.$key.'"');
    echo "update ".$key." 最后购买时间".$value.' success<br>';
}

unset($wishData);
unset($aliData);
$dbconErp->close();

$dbcon          = new DBClass();
planEnd(88,$startime_10);
$dbcon->close();
/****** E: wish  ali的销售情况，获取产品的销售时间插入到erp wish ******/



//计算并设置下单到收货的时间（天数）
if(date('D')=='Sun-----'){	//只在星期天执行,暂时不执行
    $deliverydays ="SELECT (UNIX_TIMESTAMP(a.printtime)-a.io_audittime)/86400 as goods_delivery, a.io_ordersn,a.io_audittime,a.printtime 
					FROM ebay_iostore a left join ebay_iostoredetail b on (a.io_ordersn=b.io_ordersn) 
					where a.io_status=2 and (UNIX_TIMESTAMP(a.printtime)-a.io_audittime)/86400 >1 
					and b.goods_id='$goods_id' order by a.printtime desc limit 1";
    $deliverydays		= $dbcon->execute($deliverydays);
    $deliverydays		= $dbcon->getResultArray($deliverydays);
    if(count($deliverydays)>0){
        $uptdelivery="update ebay_onhandle set goods_delivery=".$deliverydays[0]['goods_delivery']." where goods_id=$goods_id";
        $dbcon->execute($uptdelivery);
    }
}