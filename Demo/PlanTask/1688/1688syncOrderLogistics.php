<?php
/**
 * 获取 采购订单 物流跟踪号
 */

$aliOrder = new AliOrderApi();

// 查询未获取到跟踪号的采购订单
$aliOrderList = DB::Select('ali1688_order',"(logistics_bill_no='' OR logistics_bill_no IS NULL) AND status != 'cancel'",'order_id,io_ordersn');// 查询 未获取到物流单号的订单记录

//print_r($aliOrderList);exit;
foreach($aliOrderList as $order_val){
    $orderId    = $order_val['order_id'];
    $io_ordersn = $order_val['io_ordersn'];

    if(empty($orderId) OR empty($io_ordersn)) continue;
    echo '<br/>'.$orderId.':';

    $logisticsInfo = $aliOrder->getLogisticsInfo($orderId);
//    print_r($logisticsInfo);exit;

    if($logisticsInfo['result']){
        $logisticsInfo          = $logisticsInfo['result'][0];
        $logisticsId            = $logisticsInfo['logisticsId'];// 物流单编号（不是物流单号）
        $logisticsBillNo        = $logisticsInfo['logisticsBillNo'];// 物流单号
        $logisticsCompanyName   = $logisticsInfo['logisticsCompanyName'];// 物流公司名称

        if($logisticsBillNo){
            $update = array('tracknumber' => $logisticsBillNo);
            $res    = DB::Update('ebay_iostore',$update,"io_ordersn='$io_ordersn'");

            $update = array('logistics_bill_no' => $logisticsBillNo,'logistics_company_name' => $logisticsCompanyName);
            $res    = DB::Update('ali1688_order',$update,"order_id='$orderId'");
            if($res){
                AliDealHelp::addOperatorLog($orderId,'ORDER_ID','更新系统订单',"更新物流单号：$logisticsBillNo");
            }
        }

        echo '获取成功';
    }elseif($logisticsInfo['errorCode']){
        $msg = $logisticsInfo['errorMessage'];
        echo '获取失败'.$msg;
    }

}

echo '同步物流单号完成<br/>';












