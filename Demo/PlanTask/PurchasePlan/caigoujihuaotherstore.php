<?php
@session_start();
error_reporting(0);

include_once 'E:\TOOLS\htdocs\PlanTask/taskbase.php';
include_once 'common.php';

$jihuaid	= date('Y-m-d H');

subOperation("caigoujihua".date('Y-m'),'FBA开始');
/******* AMAZON-FBA 采购计划 START  *****/
include_once 'caigoujihuaforfba.php';// 运行采购计划
/******* AMAZON-FBA 采购计划 END  *****/
subOperation("caigoujihua".date('Y-m'),'FBA结束');


// 计算日均销量时，7/15/30天销量所占比重
$proportion         = PurchasePlan::getConfigProportion();
$sys_mrpdays30	    = $proportion['days30'];
$sys_mrpdays15	    = $proportion['days15'];
$sys_mrpdays7	    = $proportion['days7'];

$store_arr  = array(37);
//print_r($jihuaid);exit;

foreach($store_arr as $store_id){
    $goodsSalesStatistics = array();
    $goodsAverageDailySales = array();


    subOperation("caigoujihua".date('Y-m'),'仓库['.$store_id.']开始');
    PurchasePlan::startPlan($jihuaid,$store_id,1);


    $totalneedamount	= 0;// 需要采购金额统计
    $totalneedsku		= 0;// 需要采购SKU统计
    $totaloossku		= 0;// 已经有欠单的SKU

    if($store_id == 37){// 义乌仓库
        $skuList = "SELECT a.factory,a.goods_id,a.goods_sn,a.goods_name,a.goods_cost,a.cguser,a.salesuser,a.isuse,
                        a.lastpandian,a.lastexport,a.goods_unit,a.prepare_day 
                    FROM ebay_goods AS a 
                    WHERE 1 AND a.goods_location LIKE 'U%' ";
        //$skuList .= " AND a.goods_sn='J0308' ";

        if(date('H') != 0) {
            $skuList    .= " and (a.isuse=0 or a.isuse=3 or a.isuse=5 or a.isuse=6) ";
        }
        $skuList .= " ORDER BY a.goods_id ASC";

    }elseif($store_id == 36){// 海外虚拟仓
        $skuList    = "SELECT sku,a.factory,a.goods_id,a.goods_sn,a.goods_name,a.goods_cost,a.cguser,a.salesuser,a.isuse,
                        a.lastpandian,a.lastexport,a.goods_unit,a.prepare_day 
                     FROM ebay_storage_sku AS e_s_s
                     LEFT JOIN ebay_goods AS a ON e_s_s.sku=a.goods_sn  
                     WHERE  e_s_s.store_id=36 GROUP BY  e_s_s.sku ORDER BY  e_s_s.sku ASC  ";

    }

    $pagesize = 1000;
    for($i = 0;$i < 20000; $i += 1000){
        $sql    = $skuList." LIMIT $i,$pagesize ";
        echo $sql;echo '<br/>';

        $sql	= $dbcon->execute($sql);
        $sql	= $dbcon->getResultArray($sql);

        if(empty($sql)){
            break;
        }

        $count_now = count($sql);
        foreach($sql as $key => $sku_val){
            $addInfo        = array();

            $factory		= $sku_val['factory'];
            $goods_id		= $sku_val['goods_id'];
            $goods_sn		= $sku_val['goods_sn'];
            $goods_name		= $sku_val['goods_name'];
            $goods_cost		= $sku_val['goods_cost'];
            $isuse			= $sku_val['isuse'];
            $lastpandian	= $sku_val['lastpandian'];
            $lastexport		= $sku_val['lastexport'];
            $cguser			= $sku_val['cguser'];
            $salesuser		= $sku_val['salesuser'];

            $prepare_day    = empty($sku_val['prepare_day'])?0:$sku_val['prepare_day'];

            echo "<br>".$key."/".$count_now.'--->'.$goods_sn.'  ';

            $goods_count    = HhStock::getTotalStock($store_id,$goods_sn);
            $earliestOrder  = PurchasePlan::skuTheEarliest($goods_sn,32);// 检查这个产品最早售出的时间

            if(count($earliestOrder) > 0){
                /* 如果days 是小于或等于30的话，统一按/每天的销量 */
                $ebay_paidtime		= $earliestOrder[0]['ebay_paidtime'];
                $time3              = $mctime - $ebay_paidtime;
                $day                = floor($time3/(3600*24));

                $calculateNum = calculateSaleNum($goods_sn,$store_id);// 计算 7、15、30天的销量
                $qty7 	= $calculateNum['qty7'];
                $qty15 	= $calculateNum['qty15'];
                $qty30 	= $calculateNum['qty30'];

                if($store_id == 37){// 义乌仓读取以前坂田仓库的销量
                    $calculateNum = calculateSaleNum($goods_sn,32);// 计算 7、15、30天的销量
                    $qty7 	+= $calculateNum['qty7'];
                    $qty15 	+= $calculateNum['qty15'];
                    $qty30 	+= $calculateNum['qty30'];
                }


                $goodsSalesStatistics[$goods_sn] = array('store_id' => $store_id,'qty7' => $qty7,'qty15' => $qty15,'qty30' => $qty30);// 更新SKU的销量数据
//                print_r($goodsSalesStatistics);exit;

                $day = 31;// 设置大于30天
                if($day < 30 ){    // 如果最早售出时间小于30 的，按取得小于30 天内的总销量，在除以指定的天数
                    if($day < 7) $day = 7;	//不足7天按7天计算（通常发生在新品）byzhuwf20150328
                    $start1						= date('Y-m-d').'23:59:59';
                    $start0						= date('Y-m-d',strtotime("$start1 -$day days")).' 00:00:00';
                    $qty0						= getProductsqty($start0,$start1,$goods_sn,$store_id)/$day;		//日均售出数量
                }else{		//最早销售日大于30天
                    $qty7_2			    = ($qty7/7)     * $sys_mrpdays7;			//7天销售数量
                    $qty15_2			= ($qty15/15)   * $sys_mrpdays15;			//15天销售数量
                    $qty30_2			= ($qty30/30)   * $sys_mrpdays30;			//30天销售数量
                    $qty0				= $qty7_2 + $qty15_2 + $qty30_2;  // 根据系统设定的7、15、30天销量所占比例计算平均每天的销量
                }

                $usedstock			= stockused($goods_sn,$store_id);		//占用库存，不包括已出库和已作废的订单
                $waitforusestore	= waitforusestore($goods_sn,$store_id);		//等待分配的订单产品数:包括等待打印里未分配库存的usestore=0和非等待扫描，非已发货，非作废的订单
                $itembooked			= stockbookused($goods_sn,$store_id);	//  取得已经预订的产品数量

                list($goods_days,$purchasedays) = getGoodsAndPurDays($day,$qty0);
                $specialInfo = null;
                if(trim($salesuser) == '束从华'){// 限定产品开发员和仓库
                    $specialInfo = specialKeysFlag($specialPurDaysKeys,$sku_val);// 判断是否含有关键字
                }

                if(isset($specialInfo) AND $specialInfo['flag'] === true){
                    $beishu         = 2.5;
                    $prepare_day_count =  $qty0*$prepare_day;// 加长备货天数的需求数量
                    $zdqty 			= $qty7 * $beishu + $prepare_day_count;// 最低库存（2倍周销量）
                    $zgqty 			= $zdqty + $qty7 * $beishu + $prepare_day_count; // 最高库存
                    $goods_days 	= $specialInfo['goodDay'];
                    $purchasedays 	= $specialInfo['purDay'];
                }else{
                    $goods_days     += $prepare_day;
                    $purchasedays   += $prepare_day;

                    $zdqty 	= ceil($qty0 * $goods_days); // 最低库存
                    $zgqty	= ceil($qty0 * $purchasedays);
                }

                //处理报警天数（最低库存）及日均销量\处理采购天数（最高库存）
                $goodsAverageDailySales[$goods_sn] = array(
                    'dailysold'     => sprintf('%.3f',$qty0),
                    'goods_days'    => $goods_days,
                    'purchasedays'  => $purchasedays,
                    'storeid'       => $store_id
                );

                /*  如果实际可用库存,小于预警库存时生成采购订单 */
                $available_count = $goods_count + $itembooked-$usedstock-$waitforusestore;// 可用库存（包含采购在途）
                $actual_available_count = $goods_count - $usedstock - $waitforusestore;// 实际可用库存（不包含采购在途）

                echo $goods_count.','.$itembooked.','.$usedstock.','.$waitforusestore.','.$available_count.'-----》'.$zdqty;
                echo '<br/>';
                if( $available_count < $zdqty){

                    if(isset($specialInfo) AND $specialInfo['flag'] === true){
                        $needqty		= $zdqty; // 计划数
                    }else{
                        $needqty		= $zgqty - $available_count;	//计划数
                    }
                    if($needqty <= 0) continue;// 有一些需求数为0的跑出来，还未找原因

                    echo '<font color="green">计划数：'.$needqty."</font>";

                    $totalneedsku ++ ;	//需要采购SKU统计
                    if($goods_count + $itembooked-$usedstock-$waitforusestore <0) $totaloossku ++;	//已经有欠单的SKU
                    $totalneedamount = $totalneedamount + $needqty * $goods_cost;	//需要采购金额统计

                    $addInfo = array(
                        'jihuaid' => $jihuaid,
                        'goods_id' => $goods_id,
                        'goods_sn' => $goods_sn,
                        'goods_name' => $goods_name,
                        'factory' => $factory,
                        'zgqty' => $zgqty,
                        'storeid' => $store_id,
                        'cguser' => $cguser,
                        'goods_cost' => $goods_cost,
                        'isuse' => $isuse,
                        'lastpandian' => $lastpandian,
                        'lastexport' => $lastexport,
                        'goods_days' => $goods_days,
                        'ebay_paidtime' => $ebay_paidtime,
                        'purchasedays' => $purchasedays,
                        'prepare_day' => $prepare_day,
                        'qty0' => $qty0,
                        'itembooked' => $itembooked,
                        'usedstock' => $usedstock,
                        'waitforusestore' => $waitforusestore,
                        'zdqty' => $zdqty,
                        'goods_count' => $goods_count,
                        'needqty' => $needqty,
                        'show_type' => '10'
                    );
                }
                elseif($actual_available_count < 0){// 仓库实际缺货的产品
                    $addInfo = array(
                        'jihuaid' => $jihuaid,
                        'goods_id' => $goods_id,
                        'goods_sn' => $goods_sn,
                        'goods_name' => $goods_name,
                        'factory' => $factory,
                        'zgqty' => $zgqty,
                        'storeid' => $store_id,
                        'cguser' => $cguser,
                        'goods_cost' => $goods_cost,
                        'isuse' => $isuse,
                        'lastpandian' => $lastpandian,
                        'lastexport' => $lastexport,
                        'goods_days' => $goods_days,
                        'ebay_paidtime' => $ebay_paidtime,
                        'purchasedays' => $purchasedays,
                        'prepare_day' => $prepare_day,
                        'qty0' => $qty0,
                        'itembooked' => $itembooked,
                        'usedstock' => $usedstock,
                        'waitforusestore' => $waitforusestore,
                        'zdqty' => $zdqty,
                        'goods_count' => $goods_count,
                        'needqty' => 0,
                        'show_type' => '20'
                    );
                }

                if($addInfo){
                    $res = PurchasePlan::addPurchasePlan($addInfo);
                    unset($addInfo);
                }
            }
        }

        PurchasePlan::updateGoodsSalesStatistics($goodsSalesStatistics);// 更新SKU的销量数据
        PurchasePlan::updateGoodsAverageDailySales($goodsAverageDailySales);// 更新日均销量

    }


    $resData = array(
        'totalneedamount'   => $totalneedamount,
        'totalneedsku'      => $totalneedsku,
        'totaloossku'       => $totaloossku
    );

    PurchasePlan::endPlan($store_id,$resData,1);

    subOperation("caigoujihua".date('Y-m'),'仓库['.$store_id.']结束');

}



