<?php

/**
 * 订单处理帮助处理类
 * Class OrderDealHelp
 */
class OrderDealHelp
{
    private static $dbcon       = '';
    public static $username     = '';

    public static function init()
    {
        global $dbcon, $truename;
        self::$dbcon    = $dbcon;
        self::$username = $truename;
    }

    /**
     * 获取订单类型列表
     * @param int $class
     * @return array
     */
    public static function getOrderType($class = 0){
        self::init();

        $typeList   = "SELECT * FROM ebay_ordertype WHERE ebay_user='otw' AND class='$class' ORDER BY id";
        $typeList   = self::$dbcon->query($typeList);
        $typeList   = self::$dbcon->getResultArray($typeList);

        return empty($typeList)?array():$typeList;
    }

    /**
     * 查询一个订单
     * @param $ebay_id
     * @param bool $detail
     * @return mixed
     */
    public static function getOrder($ebay_id,$detail = false){
        self::init();

        $orderInfo = self::$dbcon->query("SELECT * FROM ebay_order WHERE ebay_id='$ebay_id' LIMIT 1 ");
        $orderInfo = self::$dbcon->getResultArray($orderInfo);
        $orderInfo = $orderInfo[0];

        if($orderInfo){
            $ebay_ordersn = $orderInfo['ebay_ordersn'];
            $detail = self::$dbcon->query("SELECT * FROM ebay_orderdetail WHERE ebay_ordersn='$ebay_ordersn' ");
            $detail = self::$dbcon->getResultArray($detail);
            $orderInfo['details'] = $detail;
        }

        return $orderInfo;
    }


}