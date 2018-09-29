<?php

include_once dirname(dirname(__FILE__)) . "/Help/DB.class.php";
include_once dirname(dirname(__FILE__)) . "/Kucun/Base/KucunAllotHelp.class.php";
include_once dirname(__FILE__) . "/IOBase.class.php";
include_once dirname(__FILE__) . "/InOutOrders.class.php";
/*
 * 2018-3-28 mxc wirte for hhcq v2
 */

/**
 * OrderConfirm 描述信息:  订单确认出库调用方法
 *
 * @author 梅小春 <476984957@qq.com>
 */
class OrderConfirmOutStock {

    //put your code here

    public static function ConfirmOutStock($ebay_id, $username, $remark) {
        $remark = $remark?$remark:"AB流程订单出库";
        //可能存在部分出库的情况，但是如果是部分出库， 流水线和单独的 A流程请勿如此操作，建议最好将部分出库的订单单独处理，此处认为已经部分出库的订单是已出库的
        $isOutStock = InOutOrders::getInOutOrder('', $ebay_id, false);
        if ($isOutStock) {  //已经出过库，禁止出
            return false;
        }
        $order = self::CreateOutOrderInfo($ebay_id, $username, $remark); //创建出库单主体
        $list = KucunAllotHelp::GetOrdersSkuAllotInfo($ebay_id);
        $SubOrders = array();
        foreach ($list as $row) {
            $sku = $row['sku'];
            $quantity = $row['allot_number'];
            $storage_sn = $row['storage_sn'];
            $store_id = $row['store_id'];
            $goods = DB::Find("ebay_goods", "goods_sn ='$sku' and storeid='$store_id'","goods_cost");
            $goods_cost = $goods['goods_cost'];
            $temp = array();
            $temp['sku'] = $sku;
            $temp['quantity'] = $quantity;
            $temp['storage_sn'] = $storage_sn;
            $temp['goods_cost'] = $goods_cost;
            $SubOrders[] = self::CreateOutOrderDetail($temp);
        }
        $order['sku_details'] = $SubOrders;
        return InOutOrders::createOutOrder($order);
    }

    /***
     * 创建主体
     */
    private static function CreateOutOrderInfo($ebay_id, $username, $remark) {
        $ebayOrder = DB::Find("ebay_order", "ebay_id=$ebay_id");
        $order = array();
        $order['order_type'] = 44; //手工出库
        $order['order_sn'] = $ebay_id;
        $order['store_id'] = $ebayOrder['ebay_warehouse'];
        $order['operator'] = $username;
        $order['remark'] = $remark;
        return $order;
    }

    /**
     * 创建子订单
     * @param type $value
     * @return type
     */
    private static function CreateOutOrderDetail($value) {
        $nowDetailTmp = array();
        $nowDetailTmp['sku'] = $value['sku'];
        $nowDetailTmp['quantity'] = $value['quantity'];
        $nowDetailTmp['storage_sn'] = $value['storage_sn'];
        $nowDetailTmp['goods_cost'] = $value['goods_cost'];
        $nowDetailTmp['total_cost'] = $value['quantity'] * $value['goods_cost'];
        return $nowDetailTmp;
    }

}
