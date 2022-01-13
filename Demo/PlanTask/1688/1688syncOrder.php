<?php
/**
 * 同步 指定账号下 1688一键下单的采购单信息
 */


$aliOrder = new AliOrderApi();

$m_time = getMillisecond();
$m_time = $m_time[1];// 获得毫秒数

// 组合查询条件
$modifyStartTime = date('YmdHis',strtotime('-7 days')).$m_time.'+0800';// 采购单修改结束时间
$page       = 1;// 分页查询的 第几页
$pageSize   = 30;// 每页的记录数

$condition = array(
    'modifyStartTime'   => $modifyStartTime,
    'page'              => $page,
    'pageSize'          => $pageSize
);

print_r($condition);
echo'<br/>';
//exit;

do{
    // 根据指定组合条件获取采购订单列表
    $orderList = $aliOrder->get1688OrderInfoList($condition);
    echo count($orderList['result']);echo'<br/>';

    $totalRecord = $orderList['totalRecord'];// 总记录数
    if(empty($orderList['result'])){// 没有记录则终止
        break;
    }

    $condition['page'] += 1;// 查询下一页
    $orderList = $orderList['result'];

//    print_r($orderList);exit;

    foreach($orderList as $order_val){
        $baseInfo           = $order_val['baseInfo'];// 订单基础信息
        $nativeLogistics    = $order_val['nativeLogistics'];// 国内物流
        $productItems       = $order_val['productItems'];// 商品条目信息
        $tradeTerms         = $order_val['tradeTerms'];// 交易条款
        $orderRateInfo      = $order_val['orderRateInfo'];// 订单评价信息

        /* $guaranteesTerms = $order_val['guaranteesTerms'];// 保障条款
        $internationalLogistics = $order_val['internationalLogistics'];// 国际物流
        $extAttributes = $order_val['extAttributes'];// 订单扩展属性 */


        $orderId        = $baseInfo['id'];// 订单ID
        $status         = trim($baseInfo['status']);// 订单状态
        $totalAmount    = $baseInfo['totalAmount'];// 订单应付款总金额（含运费），单位为元
        $shippingFee    = $baseInfo['shippingFee'];// 订单运费，单位为元
        $discount       = $baseInfo['discount'];// 折扣信息

        $oldOrderInfo   = AliDealHelp::getAli1688OrderInfo($orderId);
        $io_ordersn     = $oldOrderInfo['io_ordersn'];

//        print_r($oldOrderInfo);exit;
        if(isset($oldOrderInfo['status']) AND  $oldOrderInfo['status'] != $status){
            $update = array(
                'status'        => $status,
                'updateuser'    => 'otw',
                'updatetime'    => date('Y-m-d H:i:s')
            );

            $res = DB::Update('ali1688_order',$update,"order_id='$orderId'");
            if($res){
                $msg = "修改订单状态，从[".AliDealHelp::$orderStatus[$oldOrderInfo['status']]."]到[".AliDealHelp::$orderStatus[$status]."]";
                AliDealHelp::addOperatorLog($orderId,'ORDER_ID','更新系统订单',$msg);
            }

            // 更新采购订单
            $update_order = array('1688orderstatus' => $status);
            if(in_array($status,array('waitlogisticstakein','waitsellersend','waitbuyerreceive','等待买家签收'))){// 更新订单已付款
                $update_order['io_status'] = 3;// 在途订单
                // 设置已付款状态、付款金额、付款账号

                //获取订单总金额
                $puOrderInfo = PurchaseOrder::getPurchaseOrder(array('io_ordersn' => $io_ordersn));

                if($puOrderInfo AND empty($puOrderInfo['paystatus'])){// 为设置付款状态的采购单设置付款信息
                    $io_paidtotal = $puOrderInfo['io_paidtotal'];// 付款金额
                    $addInfo = array(
                        'io_ordersn'    => $io_ordersn,
                        'pay_time'      => time(),
                        'pay_money'     => $io_paidtotal,
                        'payer'         => '系统',
                        'remark'        => '1688订单状态更新自动设置付款'
                    );

                    $res = DB::Add('ebay_iostorepay',$addInfo);
                    if($res){
                        $update_order['paystatus'] = 1;// 付款状态
                        $update_order['accountid'] = 1007;// 付款账号ID
                        $update_order['apiPlatformType'] = '1688';// API所属平台
                    }
                }

            }elseif(in_array($status,array('cancel','terminated'))){
                $update_order['io_status'] = 6;// 作废订单
            }

            if(in_array($status,array('waitsellersend'))){
                //$update_order['paystatus'] = 1;// 已付款
            }

            if($io_ordersn){
                DB::Update('ebay_iostore',$update_order,"io_ordersn='$io_ordersn'");
                AliDealHelp::addOperatorLog($io_ordersn,'IO_ORDERSN','更新采购订单',json_encode($update_order));
            }

        }

        continue;

    }

}while(true);


echo '同步订单完成<br>';
//exit;









