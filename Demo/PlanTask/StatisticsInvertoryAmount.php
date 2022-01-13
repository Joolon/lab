<?php
header("Content-Type:text/html;   charset=utf-8");
ignore_user_abort();
set_time_limit(0);
error_reporting(0);
include 'taskbase.php';
include BASE_PATH."include/config.php";
include BASE_PATH."include/functions.php";
include_once BASE_PATH .'include/tools/arrayfunction.php';
include_once TASK_BASE_BATH . 'Common/reportfunctions.php';

$dbcon	= new DBClass();
$user   = 'otw';
$date   = date('Y-m-d');

$startime   = time();
$uptSystemTask = "update system_task set taskstatus=1,taskstarttime=".time().",RunTime=0 where ID=76 ";
$dbcon->execute($uptSystemTask);

// 查询仓库信息
$sql_store = "select id,store_name,store_sn,store_location,store_note from  ebay_store where ebay_user='$user'";
$sql_store = $dbcon->execute($sql_store);
$sql_store = $dbcon->getResultArray($sql_store);

$storeSn = get_array_column($sql_store,'store_sn','id');

/**
 * 按采购员来统计最近三个月销售订单SKU的数量
 * @param int $user_type 用户类型 100.采购员  200.开发员
 * @return boolean
 */
function group_cguser($user_type = 100){
    global $dbcon,$date;

    $months = array(
        0 => date('Y-m',strtotime(' -2 months')),
        1 => date('Y-m',strtotime(' -1 months')),
        2 => date('Y-m')
    );
    $datas = array();

    // $user_type 100.按产品采购员统计 200.按产品开发员统计
    if($user_type != 100 AND $user_type !== 200) return false;

    if($user_type == 100){
        $nameColumn = 'cguser';
        $groupBy = " GROUP BY d.cguser ";
    }elseif($user_type == 200){
        $nameColumn = 'salesuser';
        $groupBy = " GROUP BY d.salesuser ";
    }

    foreach($months as $key => $month){
        $startTime  = strtotime($month);
        $endTime    = strtotime("$month +1 month -1 day");
        $sql = "SELECT FROM_UNIXTIME( scantime,  '%Y-%m' ) AS cc, d.cguser,d.salesuser,SUM(c.ebay_amount * d.goods_cost) AS costTotal, SUM( c.ebay_itemprice * c.ebay_amount * b.rates ) + SUM( c.shipingfee * b.rates ) as total , SUM( c.ebay_amount )  as amountTotal
        FROM ebay_order a
        LEFT JOIN ebay_currency b ON ( a.ebay_currency = b.currency AND b.user =  'otw' ) 
        LEFT JOIN ebay_orderdetail c ON ( a.ebay_ordersn = c.ebay_ordersn ) 
        LEFT JOIN ebay_goods d ON ( c.sku = d.goods_sn ) 
        WHERE ebay_status=2
        AND scantime>=$startTime AND scantime<=$endTime ";

        $sql .= $groupBy;// 分组

        $data = $dbcon->query($sql);
        $data = $dbcon->getResultArray($data);
        $datas[$month] = $data;
    }

    $datasTmp = array();// 缓存每个用户 金额/数量/成本 汇总
    $cgusers = array();
    foreach($datas as $month => $value){
        foreach($value as $val){
            if(empty($val[$nameColumn])){// NULL 和 空合并
                $val[$nameColumn] = '';
                $cgusers[$val[$nameColumn]] = $val[$nameColumn];// 保存用户名
                $datasTmp[$val[$nameColumn]][$month]['amountTotal'] += $val['amountTotal'];// 总数求和
                $datasTmp[$val[$nameColumn]][$month]['total']       += $val['total'];// 金额求和
                $datasTmp[$val[$nameColumn]][$month]['costTotal']   += $val['costTotal'];// 成本求和
            }else{
                $cgusers[$val[$nameColumn]] = $val[$nameColumn];
                $datasTmp[$val[$nameColumn]][$month] = $val;
            }
        }
    }

    // 保存统计数据
    $createtime = time();
    foreach($cgusers as $cguser){

        $two_ago        = empty($datasTmp[$cguser][$months[0]]['amountTotal'])?0:$datasTmp[$cguser][$months[0]]['amountTotal'];
        $one_ago        = empty($datasTmp[$cguser][$months[1]]['amountTotal'])?0:$datasTmp[$cguser][$months[1]]['amountTotal'];
        $now            = empty($datasTmp[$cguser][$months[2]]['amountTotal'])?0:$datasTmp[$cguser][$months[2]]['amountTotal'];
        $two_total      = empty($datasTmp[$cguser][$months[0]]['total'])?0:$datasTmp[$cguser][$months[0]]['total'];
        $one_total      = empty($datasTmp[$cguser][$months[1]]['total'])?0:$datasTmp[$cguser][$months[1]]['total'];
        $now_total      = empty($datasTmp[$cguser][$months[2]]['total'])?0:$datasTmp[$cguser][$months[2]]['total'];
        $two_cost       = empty($datasTmp[$cguser][$months[0]]['costTotal'])?0:$datasTmp[$cguser][$months[0]]['costTotal'];
        $one_cost       = empty($datasTmp[$cguser][$months[1]]['costTotal'])?0:$datasTmp[$cguser][$months[1]]['costTotal'];
        $now_cost       = empty($datasTmp[$cguser][$months[2]]['costTotal'])?0:$datasTmp[$cguser][$months[2]]['costTotal'];


        $inSql = "INSERT INTO order_sku_cguser(date,two_ago,one_ago,now,two_ago_amount,one_ago_amount,now_amount,two_ago_cost,one_ago_cost,now_cost,username,createtime,user_type) 
                VALUES('$date',$two_ago,$one_ago,$now,$two_total,$one_total,$now_total,$two_cost,$one_cost,$now_cost,'$cguser','$createtime','$user_type')";
        $dbcon->execute($inSql);
    }

}

/**
 * 根据仓库统计库存金额
 */
function statistics_inventory_store(){
    global $dbcon,$user,$sql_store,$storeSn,$date;

    $storeIds = get_array_column($sql_store,'id');
    $storeIdsStr = implode(',',$storeIds);

    $storeAmount = "SELECT SUM( a.goods_cost * b.goods_count ) as cc,b.store_id
                        FROM ebay_goods AS a JOIN ebay_onhandle AS b ON a.goods_id = b.goods_id 
                        WHERE b.store_id IN($storeIdsStr)
                        GROUP BY b.store_id";
    $storeAmount = $dbcon->execute($storeAmount);
    $storeAmount = $dbcon->getResultArray($storeAmount);
    $storeAmount = get_array_column($storeAmount,'cc','store_id');

    if($storeAmount){
        // 根据仓库保存库存金额统计数据
        foreach($storeSn as $store_id => $store_sn){
            $store_amount = isset($storeAmount[$store_id])?$storeAmount[$store_id]:0;

            $insertSql = " INSERT INTO inventory_store(create_date,store_id,store_sn,store_amount) 
                      VALUES('$date','$store_id','$store_sn','$store_amount') ";
            echo $insertSql;echo '<br/>';
            $dbcon->execute($insertSql);
        }
    }
}

/**
 * 根据仓库区域下采购员统计库存金额
 * @param int $user_type 用户类型 100.采购员  200.开发员
 * @return boolean
 */
function statistics_inventory_store_amount($user_type = 100){
    global $dbcon,$date;

    // $user_type 100.按产品采购员统计 200.按产品开发员统计
    if($user_type != 100 AND $user_type !== 200)  return false;


    // 库存分滞销库存和正常库存统计
    // 滞销库存：SKU开发时间大于60天且最后出库日期超于30天或开发时间在60天内未出库的库存总金额之和
    // 正常库存：除滞销库存之外的库存
    $time60days = strtotime(' - 60 days ');
    $time30days = strtotime(' -30 days ');

    // 正常库存
    $and_where_s1 = " AND ((e_g.addtim < $time60days AND e_g.lastexport>=$time30days) OR (e_g.addtim>=$time60days AND e_g.lastexport IS NOT NULL )) ";
    // 滞销库存
    $and_where_f1 = " AND ((e_g.addtim < $time60days AND (e_g.lastexport<$time30days OR e_g.lastexport IS NULL)) 
    OR (e_g.addtim>=$time60days AND e_g.lastexport IS NULL )) ";

    $select_where_arr = array($and_where_s1,$and_where_f1);

    $datas = array();
    foreach($select_where_arr as $now_where){

        if($user_type == 100){
            $amountByUser = "SELECT aa.country,aa.cguser,aa.askuqty,aa.acc,aa.aamount,
                         bb.bskuqty,bb.bcc,bb.bamount,
                        (IFNULL(aa.aamount,0) + IFNULL(bb.bamount,0)) allamount
                    FROM (
                        SELECT e_g.cguser, IFNULL(e_u.country,'') country, COUNT( 1 )  askuqty,SUM(e_oh.goods_count) acc,
                        SUM( e_g.goods_cost * e_oh.goods_count ) aamount
                        FROM ebay_goods e_g
                        LEFT JOIN ebay_onhandle e_oh ON ( e_g.goods_sn = e_oh.goods_sn ) 
                        LEFT JOIN ebay_user e_u ON ( e_g.cguser = e_u.username ) 
                        WHERE 1  {$now_where} 
                        GROUP BY e_g.cguser
                    ) aa 
                    LEFT JOIN (
                        SELECT e_io.io_purchaseuser,IFNULL(e_u.country,'') country,COUNT(1)  bskuqty,
                        SUM(e_iod.goods_count-e_iod.goods_count0) bcc,
                        SUM(e_iod.goods_cost*(e_iod.goods_count-e_iod.goods_count0)) bamount
                        FROM ebay_iostore e_io
                        LEFT JOIN ebay_iostoredetail e_iod ON ( e_io.io_ordersn = e_iod.io_ordersn ) 
                        LEFT JOIN ebay_user e_u ON ( e_io.io_purchaseuser = e_u.username ) 
                        LEFT JOIN ebay_goods e_g ON e_g.goods_sn=e_iod.goods_sn
                        WHERE e_io.type =2 AND ( e_io.io_status =3 OR e_io.io_status =4 ) 
                        GROUP BY e_io.io_purchaseuser
                    ) bb ON ( aa.cguser = bb.io_purchaseuser ) 
                    ORDER BY country ASC,allamount DESC ";
        }elseif($user_type == 200){
            $amountByUser = "SELECT aa.country,aa.salesuser,aa.askuqty,aa.acc,aa.aamount,
                         bb.bskuqty,bb.bcc,bb.bamount,
                        (IFNULL(aa.aamount,0) + IFNULL(bb.bamount,0)) allamount
                    FROM (
                        SELECT e_g.salesuser, IFNULL(e_u.country,'') country, COUNT( 1 )  askuqty,SUM(e_oh.goods_count) acc,
                        SUM( e_g.goods_cost * e_oh.goods_count ) aamount
                        FROM ebay_goods e_g
                        LEFT JOIN ebay_onhandle e_oh ON ( e_g.goods_sn = e_oh.goods_sn ) 
                        LEFT JOIN ebay_user e_u ON ( e_g.salesuser = e_u.username ) 
                        WHERE 1  {$now_where} 
                        GROUP BY e_g.salesuser
                    ) aa 
                    LEFT JOIN (
                        SELECT e_g.salesuser,IFNULL(e_u.country,'') country,COUNT(1)  bskuqty,
                        SUM(e_iod.goods_count-e_iod.goods_count0) bcc,
                        SUM(e_iod.goods_cost*(e_iod.goods_count-e_iod.goods_count0)) bamount
                        FROM ebay_iostore e_io
                        LEFT JOIN ebay_iostoredetail e_iod ON ( e_io.io_ordersn = e_iod.io_ordersn ) 
                        LEFT JOIN ebay_goods e_g ON e_g.goods_sn=e_iod.goods_sn
                        LEFT JOIN ebay_user e_u ON ( e_g.salesuser = e_u.username ) 
                        WHERE e_io.type =2 AND ( e_io.io_status =3 OR e_io.io_status =4 ) 
                        GROUP BY e_g.salesuser
                    ) bb ON ( aa.salesuser = bb.salesuser ) 
                    ORDER BY country ASC,allamount DESC ";
        }

//        echo $amountByUser;echo '<br/>';exit;


        $amountByUser = $dbcon->query($amountByUser);
        $amountByUser = $dbcon->getResultArray($amountByUser);

        if($user_type == 100){
            $amountByUser = arrayColumnsToKey($amountByUser,'cguser');
        }elseif($user_type == 200){
            $amountByUser = arrayColumnsToKey($amountByUser,'salesuser');
        }

        $datas[] = $amountByUser;
    }

//    print_r($datas);exit;

    if($datas){
        // 根据查询得到的数据获取用户列表
        $user1 = array_keys($datas[0]);
        $user2 = array_keys($datas[1]);
        $userArr = array_merge($user1,$user2);
        $userArr = array_unique($userArr);

        foreach ($userArr as $user_now){
            $now_data0 = $datas[0][$user_now];
            $now_data1 = $datas[1][$user_now];
            $now_country = empty($now_data0['country'])?$now_data1['country']:$now_data0['country'];

            // 正常库存
            $now_s_askuqty  = isset($now_data0['askuqty'])?$now_data0['askuqty']:0;
            $now_s_acc      = isset($now_data0['acc'])?$now_data0['acc']:0;
            $now_s_aamount  = isset($now_data0['aamount'])?$now_data0['aamount']:0;
            // 滞销库存
            $now_f_askuqty  = isset($now_data1['askuqty'])?$now_data1['askuqty']:0;
            $now_f_acc      = isset($now_data1['acc'])?$now_data1['acc']:0;
            $now_f_aamount  = isset($now_data1['aamount'])?$now_data1['aamount']:0;

            // 在途库存

            $now_bskuqty  = isset($now_data1['bskuqty'])?$now_data1['bskuqty']:0;
            $now_bcc      = isset($now_data1['bcc'])?$now_data1['bcc']:0;
            $now_bamount  = isset($now_data1['bamount'])?$now_data1['bamount']:0;

            // 总库存金额
            $allamount      = isset($now_data0['allamount'])?$now_data0['allamount']:0;
            $now_allamount  = $allamount + $now_f_aamount;

            $insertSql = "INSERT INTO statistics_inventory_store_amount(date,cguser,country,s_askuqty,s_acc,s_aamount,f_askuqty,f_acc,f_aamount,bskuqty,bcc,bamount,allamount,user_type)
                VALUES('$date','$user_now','$now_country','$now_s_askuqty','$now_s_acc','$now_s_aamount','$now_f_askuqty','$now_f_acc','$now_f_aamount','$now_bskuqty','$now_bcc','$now_bamount','$now_allamount','$user_type')";

            echo $insertSql;echo '<br/>';
            $dbcon->execute($insertSql);

        }

    }

}

/**
 * 根据采购单审核时间统计每个采购员每天 SKU的种类和PCS
 * 根据审核时间
 */
function statistics_iostore_by_cguser_audittime(){
    global $dbcon,$date;
    $createtime     = time();

    $days = -1;// 往前推14天

    for($i = $days;$i < 0 ;$i ++ ){
        $now_date   = date('Y-m-d',strtotime(" $i day"));

        echo $now_date;echo "<br/>";
//        continue;

        $userCount      = getCaiGouOrderOrderSkuCount($now_date,$now_date,'audit_time');// 获取采购员下采购单SKU种数和总PCS数
        $sqlSelOrder    = getCaiGouOrderOrderCount($now_date,$now_date,'audit_time');// 获取采购员下采购单个数
        $sqlSelOrder    = get_array_column($sqlSelOrder,'order_count','cguser');

        if($userCount){
            foreach($userCount as $key => $value){
                $username       = $value['cguser'];
                $country        = $value['country'];
                $order_count    = isset($sqlSelOrder[$username])?$sqlSelOrder[$username]:0;
                $sku_count      = $value['sku_count'];
                $sku_total      = $value['sku_total'];

                $inSql = "INSERT INTO order_sku_cguser_caigou(date,username,country,order_count,sku_count,sku_total,createtime) 
                VALUES('$now_date','$username','$country','$order_count','$sku_count','$sku_total','$createtime')";
                $dbcon->execute($inSql);
            }
        }
    }

}


/**
 * 根据采购单创建时间统计每个采购员每天 SKU的种类和PCS
 * 根据创建时间
 */
function statistics_iostore_by_cguser_addtime(){
    global $dbcon,$date;
    $createtime     = time();

    $days = -1;// 往前推14天

    for($i = $days;$i < 0 ;$i ++ ){
        $now_date   = date('Y-m-d',strtotime(" $i day"));

        echo $now_date;echo "<br/>";
//        continue;

        $userCount      = getCaiGouOrderOrderSkuCount($now_date,$now_date,'add_time');// 获取采购员下采购单SKU种数和总PCS数
        $sqlSelOrder    = getCaiGouOrderOrderCount($now_date,$now_date,'add_time');// 获取采购员下采购单个数
        $sqlSelOrder    = get_array_column($sqlSelOrder,'order_count','cguser');

        $mrpSkuCount    = getMrpNeedQtyByUser($now_date);// 获取指定日期下 采购员 MRP自动计算的采购需求数
        $mrpSkuCount    = arrayColumnsToKey($mrpSkuCount,'cguser');

        if($userCount){
            foreach($userCount as $key => $value){
                $username       = $value['cguser'];
                $country        = $value['country'];
                $order_count    = isset($sqlSelOrder[$username])?$sqlSelOrder[$username]:0;
                $sku_count      = $value['sku_count'];
                $sku_total      = $value['sku_total'];

                $mrp_sku_count  = isset($mrpSkuCount[$username])?$mrpSkuCount[$username]['sku_count']:0;
                $mrp_sku_total  = isset($mrpSkuCount[$username])?$mrpSkuCount[$username]['sku_total']:0;

                $inSql = "INSERT INTO order_sku_cguser_caigou_addtime(date,username,country,order_count,sku_count,sku_total,mrp_sku_count,mrp_sku_total,createtime) 
                          VALUES('$now_date','$username','$country','$order_count','$sku_count','$sku_total','$mrp_sku_count','$mrp_sku_total','$createtime')";
                $dbcon->execute($inSql);

            }
        }
    }

}


echo time();echo '<br/>';

group_cguser(100);// 根据采购员统计
group_cguser(200);// 根据开发员统计

statistics_inventory_store();

statistics_inventory_store_amount(100);// 根据采购员统计
statistics_inventory_store_amount(200);// 根据开发员统计

statistics_iostore_by_cguser_audittime();

statistics_iostore_by_cguser_addtime();

echo time();


$usetime        = (time()-$startime)/60;
$uptSystemTask  = "update system_task set Runtime=".time().",UseTime=".$usetime.",taskstatus=0 where ID=76 ";
$dbcon->execute($uptSystemTask);