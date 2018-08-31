<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/18
 * Time: 15:57
 */


/**
 * 根据采购员获取MRP需求SKU 种数和PCS总数
 * @param $date
 * @return mixed
 */
function getMrpNeedQtyByUser($date){
    global $dbcon;
    $jihuaid = $date.' 06';// 读取每天6点的采购计划

    // 10.类型采购计划（区分于仓库缺货类型）
    $sqlSel = "SELECT cguser,count(1) AS sku_count,SUM(needqty) AS sku_total
    FROM ebay_cgjh 
    WHERE show_type=10 AND jihuaid='$jihuaid'
    GROUP BY cguser ";

    $skuCount = $dbcon->query($sqlSel);
    $skuCount = $dbcon->getResultArray($skuCount);

    return $skuCount;
}


/**
 * 统计指定日期范围内 采购员下单SKU种数和SKU PCS数量
 * @param string $start_date 开始日期
 * @param string $end_date 结束日期
 * @param string $time_type  以采购单审核时间或添加时间维度统计
 * @return array|resource
 */
function getCaiGouOrderOrderSkuCount($start_date,$end_date,$time_type = 'audit_time'){
    global $dbcon;

    $start_time  = strtotime(" $start_date 00:00:00 ");
    $end_time    = strtotime(" $end_date 23:59:59 ");

    $where = '';
    if($time_type == 'add_time'){// 时间类型为添加时间
        $where = " AND e_io.io_addtime>=$start_time AND e_io.io_addtime<=$end_time ";
    }elseif($time_type = 'audit_time'){// 时间类型为审核时间
        $where = " AND e_io.io_audittime>=$start_time AND e_io.io_audittime<=$end_time ";
    }

    // io_status!=6 作废  type=2入库类型  io_addtime审核时间
    $sqlSel = " SELECT e_u.country,io_purchaseuser as cguser,count(1) AS sku_count,sum(total_count) as sku_total
            FROM (
                SELECT  e_io.io_purchaseuser,e_iod.goods_sn,sum(e_iod.goods_count) as total_count
                FROM ebay_iostore AS e_io
                LEFT JOIN ebay_iostoredetail AS e_iod ON e_io.io_ordersn=e_iod.io_ordersn
                WHERE e_io.type=2 AND e_io.io_status!=6
                {$where}
                GROUP BY  e_io.io_purchaseuser,e_iod.goods_sn
            ) AS aa 
            LEFT JOIN ebay_user AS e_u ON e_u.username=aa.io_purchaseuser
            GROUP BY io_purchaseuser
            ORDER BY e_u.country ASC,io_purchaseuser ASC ";

//    echo $sqlSel;exit;

    $userCount = $dbcon->query($sqlSel);
    $userCount = $dbcon->getResultArray($userCount);

    return $userCount;
}


/**
 * 统计指定日期范围内 采购员下采购单的个数(采购单数量)
 * @param string $start_date 开始日期
 * @param string $end_date 结束日期
 * @param string $time_type  以采购单审核时间或添加时间维度统计
 * @return array|resource|string
 */
function getCaiGouOrderOrderCount($start_date,$end_date,$time_type = 'audit_time'){
    global $dbcon;

    $start_time  = strtotime(" $start_date 00:00:00 ");
    $end_time    = strtotime(" $end_date 23:59:59");

    $where = '';
    if($time_type == 'add_time'){// 时间类型为添加时间
        $where = " AND e_io.io_addtime>=$start_time AND e_io.io_addtime<=$end_time ";
    }elseif($time_type = 'audit_time'){// 时间类型为审核时间
        $where = " AND e_io.io_audittime>=$start_time AND e_io.io_audittime<=$end_time ";
    }

    // 统计采购单个数
    // io_status!=6 作废  type=2 入库类型
    $orderCount = " SELECT io_purchaseuser as cguser,count(1) as order_count  
            FROM(
                SELECT  e_io.io_purchaseuser,e_io.io_ordersn
                FROM ebay_iostore AS e_io
                LEFT JOIN ebay_iostoredetail AS e_iod ON e_io.io_ordersn=e_iod.io_ordersn
                WHERE e_io.type=2 AND e_io.io_status!=6
                {$where}
                GROUP BY  e_io.io_purchaseuser,e_io.io_ordersn 
            ) AS aa GROUP BY io_purchaseuser ";
    $orderCount = $dbcon->query($orderCount);
    $orderCount = $dbcon->getResultArray($orderCount);

    return $orderCount;

}