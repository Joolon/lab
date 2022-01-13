<?php
include 'taskbase.php';
include_once BASE_PATH."include/dbconnect.php";
include_once BASE_PATH."include/tools/arrayfunction.php";

date_default_timezone_set('Asia/Shanghai');
error_reporting(0);
set_time_limit(0);
$dbcon      = new DBClass();

$startime	= time();
$dbcon->execute("update system_task set taskstatus=1,taskstarttime=".time().",RunTime=0 where ID=82 ");



$currencyList	= 'select * from ebay_currency where user="otw" ';
$currencyList	= $dbcon->execute($currencyList);
$currencyList	= $dbcon->getResultArray($currencyList);
$currencyList   = arrayColumnsToKey($currencyList,'currency');
// print_r($currencyList);EXIT;


$orderTypeStr = " AND e_o.ebay_ordertype IN('EBAY订单','EBAY订单-US','EBAY订单-UK','EBAY订单-AU','JOOM','1688','复制订单')";

/************  START : 计算 清仓产品中已发货销售订单的产品明细（SKU）的分摊的成本、运费、邮费、PP费用等 **********/

$days = -2;

for($i = $days;$i < 0;$i += 2){
    
    $search_start   = strtotime(date('Y-m-d',strtotime(" $i days")));
//    $search_end     = strtotime(date('Y-m-d 23:59:59',strtotime(" $i days")));
    $search_start2  = strtotime(date('Y-m-d',strtotime(' -4 days')));
    
    echo '执行日期：'.date('Y-m-d H:i:s',$search_start).'~'.date('Y-m-d H:i:s',$search_end);
    echo '<br/>';
//     continue;
//    exit;
    
    // 查询最近两天内发货，且没有加入到最近四天内没有加入到 统计缓存表中的订单
    $sqlSel = " SELECT e_o.ebay_id,e_o.ebay_ordersn,e_o.ebay_ordertype,e_o.ebay_account,e_o.ebay_currency,e_o.paypalfees,e_o.orderweight,e_o.orderweight2,
            e_o.ebay_carrier,e_o.ebay_countryname,e_o.system_shippingcarriername,
            e_o.ebay_addtime,e_o.ebay_paidtime,e_o.ShippedTime,e_o.scantime,e_o.aa_postage,e_o.aa_profit,
            e_od.sku,e_o.ebay_status,e_o.ebay_total,e_od.ebay_amount,e_od.FinalValueFee,e_od.FeeOrCreditAmount,e_od.ebay_amount,e_od.ebay_itemprice,
            e_g.goods_cost,e_g.goods_weight,e_g.goods_weight2,e_g.isuse,e_g.cguser,e_g.salesuser
        FROM ebay_order AS e_o 
        LEFT JOIN ebay_orderdetail AS e_od ON e_od.ebay_ordersn=e_o.ebay_ordersn
        LEFT JOIN ebay_goods AS e_g ON e_g.goods_sn=e_od.sku
        WHERE e_o.ebay_status=2 
         ".$orderTypeStr."
         AND e_g.goods_note LIKE '%【集中清仓2017年10月26日修改产品状态为清仓】%'
         AND e_o.scantime>=$search_start
         AND e_o.ebay_id NOT IN(SELECT ebay_id FROM cache_save_kuiben_bysku WHERE add_time>$search_start2 ) ";
    
//    echo $sqlSel;exit;
    
    $orderLists = $dbcon->execute($sqlSel);
    $orderLists = $dbcon->getResultArray($orderLists);
    
//    print_r(count($orderLists));
//    exit;
    
    
    foreach($orderLists as $order){
        $ebay_id        = $order['ebay_id'];
        $ebay_ordersn   = $order['ebay_ordersn'];
        $sku            = $order['sku'];
        $ebay_amount    = $order['ebay_amount'];
        $aa_postage     = $order['aa_postage'];
        $aa_profit      = $order['aa_profit'];
    
        $ebay_currency  = trim($order['ebay_currency']);
        $rates          = !empty($currencyList[$ebay_currency]['onlinerates'])?$currencyList[$ebay_currency]['onlinerates']:1;
        $ebay_itemprice = $order['ebay_itemprice'] * $rates;
        $ebay_total 	= $order['ebay_total'] * $rates;
    
        // 当前 SKU的刊登费
        $ebayfeesFrode  = $order['FinalValueFee'];// 刊登费
        $ebayfees	    = $ebayfeesFrode * $rates;
        // 当前 SKU的PP费
        $paypalfeesFrode = $order['FeeOrCreditAmount'];// PP 费
        $paypalfees	     = ($paypalfeesFrode + $order['paypalfees']) * $rates;	//addbyzhuwf20160714


        // 重量比例(SKU所占订单总重量的比例)
        $orderweight    = empty($order['orderweight2'])?$order['orderweight']:$order['orderweight2'];// 订单包裹总重量
        $goods_weight   = empty($order['goods_weight2'])?$order['goods_weight']:$order['goods_weight2'];// 单个SKU的重量
    
        
        // 读取最新一条成本核算的数据(从后勤出库操作中读取)
        $save_kuiben    = "SELECT * FROM save_kuiben WHERE ebay_id='$ebay_id' 
                    AND file_url IN('/pickOrder/AjaxWeight.php','/scan/scanorder_01.php','/scan/scanorder_03.php',
                    '/zPrintlabelNew1010_1.php','/zPrintlabelNew1010_2.php','/zPrintlabelNew1015.php','/zPrintlabelNewHaiwai.php') 
                    ORDER BY id DESC LIMIT 1 ";
        $save_kuiben    = $dbcon->query($save_kuiben);
        $save_kuiben    = $dbcon->getResultArray($save_kuiben);
        if(empty($save_kuiben)){// 没有的话就随便读一条最新的
            $save_kuiben    = "SELECT * FROM save_kuiben WHERE ebay_id='$ebay_id' ORDER BY id DESC LIMIT 1 ";
            $save_kuiben    = $dbcon->query($save_kuiben);
            $save_kuiben    = $dbcon->getResultArray($save_kuiben);
//            if(empty($save_kuiben)){
//                $ebay_carrier = strtoupper(trim($order['ebay_carrier']));
//                $ebay_countryname = trim($order['ebay_countryname']);
//                $aa_postage = shipfeecalcByqudao($ebay_carrier, $orderweight, $ebay_countryname, $order['system_shippingcarriername']);
//            }
        }

        $package_cost   = $save_kuiben[0]['package_cost'];// 包裹总成本（含有SKU库存成本）
        $shipping_fees  = $save_kuiben[0]['shipping_fees'];// 总运费
//       print_r($save_kuiben);exit;

    
    //    print_r($shipping_fees);exit;
    
        // 获取订单 产品明细实际 总库存成本、总重量
        $orderSku = "SELECT sum(e_od.ebay_amount * e_g.goods_cost) as totalSkuCost,
                    sum(e_od.ebay_amount * e_g.goods_weight) as totalSkuWeight,
                    sum(e_od.ebay_amount * e_g.goods_weight2) as totalSkuWeight2
                FROM ebay_orderdetail AS e_od 
                LEFT JOIN ebay_goods AS e_g ON e_g.goods_sn=e_od.sku
                WHERE e_od.ebay_ordersn='$ebay_ordersn' ";
        $orderSku = $dbcon->query($orderSku);
        $orderSku = $dbcon->getResultArray($orderSku);
        $orderSku = $orderSku[0];
        $totalSkuCost = empty($orderSku['totalSkuCost'])?0:$orderSku['totalSkuCost'];
        $totalSkuWeight = empty($orderSku['totalSkuWeight'])?0:$orderSku['totalSkuWeight'];
        $totalSkuWeight2 = empty($orderSku['totalSkuWeight2'])?0:$orderSku['totalSkuWeight2'];
    
    //    print_r($totalSkuWeight);exit;
        $now_sku_weight = $goods_weight * $ebay_amount;// 订单中当前SKU的重量
        $orderweight_tmp = 0;// 包裹实际SKU的总重量（不加包材）
        if($totalSkuWeight){
            $orderweight_tmp = $totalSkuWeight;
        }elseif($totalSkuWeight2){
            $orderweight_tmp = $totalSkuWeight2;
        }else{
            $orderweight_tmp = $orderweight;
        }
    
    
        // 计算 当前SKU 所占的 包材成本
        $sku_package_cost = $package_cost - $totalSkuCost;// 包材总成本 = 包裹总成本 - SKU库存成本
        $now_sku_package_cost = ($now_sku_weight / $orderweight_tmp) * $sku_package_cost;// 当前SKU所占包材成本
        if($now_sku_package_cost > $sku_package_cost){ // 当前SKU包材成本 不能大于 包材总成本
            $now_sku_package_cost = $sku_package_cost;
        }
        // 计算当前SKU 所占的 运费
        $sku_shipping_fees    = ($now_sku_weight / $orderweight_tmp) * $shipping_fees;// SKU所占运费
        if($sku_shipping_fees > $shipping_fees){ // 当前SKU分摊的运费 不能大于 总运费
            $sku_shipping_fees = $shipping_fees;
        }
        $bili = ($ebay_itemprice * $ebay_amount - $ebayfees - $paypalfees - $sku_package_cost - $sku_shipping_fees) / ($order['goods_cost'] * $ebay_amount);
    
    
        $sqlInt = " INSERT INTO cache_save_kuiben_bysku(ebay_id,ebay_ordertype,ebay_account,ebay_status,sku,sku_isuse,sku_cguser,sku_salesuser,ebay_total,
                  sku_amount,ebay_itemprice,sku_cost,ebay_fees,paypal_fees,package_cost,shipping_fees,bili,ebay_addtime,ebay_paidtime,ShippedTime,scantime,add_time) 
             VALUES ('$ebay_id','".$order['ebay_ordertype']."','".$order['ebay_account']."','".$order['ebay_status']."','".$sku."','".$order['isuse']."',
             '".$order['cguser']."','".$order['salesuser']."','".$ebay_total."','".$ebay_amount."','$ebay_itemprice','".$order['goods_cost']."','$ebayfees',
             '$paypalfees','$sku_package_cost','$sku_shipping_fees','$bili','".$order['ebay_addtime']."','".$order['ebay_paidtime']."','".$order['ShippedTime']."','".$order['scantime']."','$startime') ";
    
//        echo $sqlInt;exit;
    
        $dbcon->execute($sqlInt);
    
    }


    break;
}

/************  END : 计算 清仓产品中已发货销售订单的产品明细（SKU）的分摊的成本、运费、邮费、PP费用等 **********/


//echo '<br/><br/><br/>';
// echo 123;exit;



/************  START : 计算 清仓产品中已发货销售订单的产品的出库数量、成本、销售额等汇总数据 **********/

$selData = "SELECT max(add_time) as max_date FROM cache_save_kuiben_gather WHERE data_type=1  ";
$selData = $dbcon->query($selData);
$selData = $dbcon->getResultArray($selData);
$selData = $selData[0]['max_date'];
echo $selData;echo '<br/>';

$second1 = empty($selData)?strtotime('2017-09-20'):strtotime($selData);
$second2 = time();

$days = floor( ($second2 - $second1) / 86400);// 上一次插入数据与当天日期相差的天数
//echo $days;exit;
if($days > 1){
    $before_days = - $days + 1 ;
}else{
    $before_days = 0;// 设为0不执行
}

//echo $before_days;echo '<br/>';


for($i = $before_days; $i < 0;$i ++){
    $start_time = strtotime(date('Y-m-d',strtotime(" $i days ")));
    $end_time   = strtotime(date('Y-m-d 23:59:59',strtotime(" $i days ")));
    $now_date   = date('Y-m-d',$start_time);// 统计日期

    echo '执行日期：'.$now_date.'：'.$start_time."~".$end_time;echo '<br/>';
//    continue;


    // 根据平台和SKU分组统计
    $goodsList = "SELECT e_o.ebay_ordertype,e_g.goods_sn,e_g.goods_cost,
                sum(e_od.ebay_amount) AS total_amount,
                sum(e_od.ebay_amount * e_od.ebay_itemprice * e_c.rates) AS total_money,
                sum(e_g.goods_cost * e_od.ebay_amount) AS total_cost,
                sum(e_od.ebay_amount * e_od.ebay_itemprice * e_c.rates) - sum(e_g.goods_cost * e_od.ebay_amount) AS gross_profit
            FROM ebay_goods AS e_g 
            LEFT JOIN ebay_orderdetail AS e_od ON e_g.goods_sn=e_od.sku
            LEFT JOIN ebay_order AS e_o ON e_o.ebay_ordersn=e_od.ebay_ordersn
            LEFT JOIN ebay_currency AS e_c ON (e_c.currency=e_o.ebay_currency AND e_c.user='otw')
            WHERE  e_g.`goods_note` LIKE '%【集中清仓2017年10月26日修改产品状态为清仓】%'
            AND e_o.ebay_status=2
            ".$orderTypeStr."
            AND e_o.scantime >= $start_time AND  e_o.scantime <= $end_time 
            GROUP BY e_o.ebay_ordertype,e_g.goods_sn ";

//   echo $goodsList;exit;

    $goodsList = $dbcon->query($goodsList);
    $goodsList = $dbcon->getResultArray($goodsList);

//    print_r($goodsList);echo '<br/>';

    $sku_arr        = get_array_column($goodsList,'goods_sn');
    $sku_arr_str    = implode("','",$sku_arr);
//    echo $sku_arr_str; echo '<br/>';

    // 根据平台和SKU分组统计 各项费用的汇总
    $goods_other_fee_list = "SELECT ebay_ordertype,sku,sum(ebay_fees) as total_ebay_fees,sum(paypal_fees) as total_paypal_fees,
        sum(package_cost) as total_package_cost,sum(shipping_fees) as total_shipping_fees
        FROM cache_save_kuiben_bysku 
        WHERE scantime >= $start_time AND scantime <= $end_time 
        AND sku IN('$sku_arr_str') 
        GROUP BY ebay_ordertype,sku ";

    $goods_other_fee_list = $dbcon->query($goods_other_fee_list);
    $goods_other_fee_list = $dbcon->getResultArray($goods_other_fee_list);
    $goods_other_fee_list_tmp = array();
    foreach($goods_other_fee_list as $list){
        $goods_other_fee_list_tmp[$list['ebay_ordertype']][$list['sku']] = $list;
    }
    $goods_other_fee_list = $goods_other_fee_list_tmp;
    unset($goods_other_fee_list_tmp);

    foreach($goodsList as $value){
        $ebay_ordertype = $value['ebay_ordertype'];
        $goods_sn       = $value['goods_sn'];
        $goods_cost     = $value['goods_cost'];
        $total_amount   = $value['total_amount'];
        $total_money    = $value['total_money'];
        $total_cost     = $value['total_cost'];
        $gross_profit   = $value['gross_profit'];

        // 当前SKU各项费用汇总
        $goods_other_fee        = isset($goods_other_fee_list[$ebay_ordertype][$goods_sn])?$goods_other_fee_list[$ebay_ordertype][$goods_sn]:array();

        $total_ebay_fees        = empty($goods_other_fee['total_ebay_fees'])?0:$goods_other_fee['total_ebay_fees'];
        $total_paypal_fees      = empty($goods_other_fee['total_paypal_fees'])?0:$goods_other_fee['total_paypal_fees'];
        $total_package_cost     = empty($goods_other_fee['total_package_cost'])?0:$goods_other_fee['total_package_cost'];
        $total_shipping_fees    = empty($goods_other_fee['total_shipping_fees'])?0:$goods_other_fee['total_shipping_fees'];
        $total_qijian_fees      = $total_ebay_fees + $total_paypal_fees + $total_package_cost + $total_shipping_fees;// 期间费


        $sqlInsert = "INSERT INTO cache_save_kuiben_gather(ebay_ordertype,sku,goods_cost,total_cost,total_amount,total_money,gross_profit,
                total_ebay_fees,total_paypal_fees,total_package_cost,total_shipping_fees,total_qijian_fees,data_type,add_time)
                VALUE('$ebay_ordertype','$goods_sn','$goods_cost','$total_cost','$total_amount','$total_money','$gross_profit',
                '$total_ebay_fees','$total_paypal_fees','$total_package_cost','$total_shipping_fees','$total_qijian_fees',1,'$now_date') ";

//        echo $sqlInsert;echo '<br/>';
        $dbcon->execute($sqlInsert);
    }


}

/************  END : 计算 清仓产品中已发货销售订单的产品当天的出库数量、成本、销售额等汇总数据 **********/


/****************************************  START : 计算 SKU的退款金额 *******************************/

$order_plats = "'EBAY','JOOM'";
$all_order_table = array(
        array('ebay_order','ebay_orderdetail'),
        array('ebay_order_HistoryRcd','ebay_orderdetail_HistoryRcd')
    );

$days = -30;
for($j = $days;$j < 0;$j ++){
    $start_date = date('Y-m-d 00:00:00',strtotime(" $j days "));
    $end_date   = date('Y-m-d 23:59:59',strtotime(" $j days "));
    $add_date   = date('Y-m-d',strtotime(' -1000 days'));
    $now_date   = date('Y-m-d',strtotime($start_date));// 统计日期

    echo '执行日期：'.$now_date.'：'.$start_date."~".$end_date;echo '<br/>';
    continue;

    foreach($all_order_table as $value_table){
        $table_0 = $value_table[0];
        $table_1 = $value_table[1];


        $refundList = "SELECT  refd.orders_plat,refd.ebay_id,sum(refd.amount) AS refd_total_amount,refd.currency,
                e_o.ebay_ordersn,e_o.ebay_ordertype
            FROM ebay_paypal_refund_analy AS refd
            INNER JOIN $table_0 AS e_o ON e_o.ebay_id=refd.ebay_id
            WHERE refd.refund_time>='$start_date' AND refd.refund_time<='$end_date'
            AND refd.orders_plat IN($order_plats)
            GROUP BY refd.ebay_id ";
//        echo $refundList;echo'<br>';
//        continue;

        // 根据订单SKU的销售额 计算所占退款总金额的比例
        $refundList = $dbcon->query($refundList);
        $refundList = $dbcon->getResultArray($refundList);
//        print_r($refundList);exit;

        foreach($refundList as $value_refd){
            $orders_plat        = $value_refd['orders_plat'];
            $ebay_id            = $value_refd['ebay_id'];
            $ebay_ordersn       = $value_refd['ebay_ordersn'];
            $ebay_ordertype     = $value_refd['ebay_ordertype'];
            $refd_total_amount  = $value_refd['refd_total_amount'];
            $currency           = $value_refd['currency'];
            $rates              = !empty($currencyList[$currency]['rates'])?$currencyList[$currency]['rates']:1;
            $refd_total_amount  = $refd_total_amount * $rates;


            $orderSkuList = "SELECT e_od.sku,sum(e_od.ebay_amount * e_od.ebay_itemprice) AS sku_total_amount
                FROM $table_1 AS e_od 
                LEFT JOIN ebay_goods AS e_g ON e_g.goods_sn=e_od.sku
                WHERE e_od.ebay_ordersn='$ebay_ordersn'
                GROUP BY e_od.sku ";
//            print_r($orderSkuList);exit;
            $orderSkuList = $dbcon->query($orderSkuList);
            $orderSkuList = $dbcon->getResultArray($orderSkuList);
//            print_r($orderSkuList);exit;

            $sku_total_amount_list  = get_array_column($orderSkuList,'sku_total_amount');
            $total_amount           = array_sum($sku_total_amount_list);// 订单明细SKU的总金额

            foreach($orderSkuList as $orderSku){
                $now_sku = $orderSku['sku'];
                $now_sku_total_amount = $orderSku['sku_total_amount'];

                $now_sku_refd_amount = ( $now_sku_total_amount / $total_amount ) * $refd_total_amount;// SKU销售额所占订单总销售额的比例
                $sqlCheckSku = "SELECT goods_id FROM ebay_goods WHERE goods_sn='$now_sku' AND goods_note LIKE '%【集中清仓2017年10月26日修改产品状态为清仓】%' LIMIT 1 ";
//                echo $sqlCheckSku;exit;
                $sqlCheckSku = $dbcon->query($sqlCheckSku);
                $sqlCheckSku = $dbcon->getResultArray($sqlCheckSku);
//                print_r($sqlCheckSku);exit;

                $now_sku_refd_amount = abs($now_sku_refd_amount);
                if($sqlCheckSku AND $now_sku_refd_amount > 0){
                    $sqlInsert = "INSERT INTO cache_save_kuiben_gather(ebay_ordertype,sku,goods_cost,total_cost,total_amount,total_money,gross_profit,
                            total_ebay_fees,total_paypal_fees,total_package_cost,total_shipping_fees,total_qijian_fees,total_refund_amount,data_type,add_time)
                        VALUE('$ebay_ordertype','$now_sku','0','0','0','0','0',
                        '0','0','0','0','0','$now_sku_refd_amount',3,'$now_date') ";
//                    echo $sqlInsert;echo '<br>';
//                    exit;

                    $dbcon->execute($sqlInsert);
                }
//                print_r($sqlCheckSku);exit;
            }
        }

    }

}



/**************************************  END : 计算 SKU的退款金额 ************************************/


 $usetime    = (time()-$startime)/60;
 $detail     = '';
 $dbcon->execute("update system_task set Runtime=".time().",UseTime=".$usetime.",taskstatus=0 where ID=82 ");

 echo '<font color="green">'.$detail.'</font>';
 exit;

