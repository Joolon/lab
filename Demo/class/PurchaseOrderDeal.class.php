<?php

/**
 * 采购单 处理辅助操作类
 * Class PurchaseOrderDeal
 */
class PurchaseOrderDeal{
    private static $_dbconn = null;// 数据库连接

    public function __construct(){
        global $dbcon;
        self::$_dbconn = $dbcon;
    }

    /**
     * @param string $sku  SKU编码
     * @return array  该SKU最新的订单的物流渠道和订单类型
     */
    public function getLastPurPlanTypeBySku($sku){
        $sku = mysql_escape_string($sku);

        $sql = "SELECT e_o.ebay_carrier,e_o.ebay_ordertype,e_o.ebay_account
        FROM ebay_orderdetail AS e_od 
        INNER JOIN ebay_order AS e_o ON e_o.ebay_ordersn=e_od.ebay_ordersn
        WHERE e_od.sku='$sku' AND e_o.ebay_paidtime>0 AND ebay_carrier !='' 
        AND ebay_ordertype != '' AND ebay_status !=236
        ORDER BY e_o.ebay_id DESC LIMIT 1";

        $sql = self::$_dbconn->query($sql);
        $sql = self::$_dbconn->getResultArray($sql);
        return empty($sql)?array():$sql[0];
    }


    /**
     * 获取指定 SKU 的最近发货渠道和订单平台 设置的采购单的 SKU 的类型
     * @param string $sku SKU编码
     * @return array
     */
    public function getPurPlanOrderType($sku){
        $l_o_type = 0;
        $result  = $this->getLastPurPlanTypeBySku($sku);// 获得SKU最近的发货渠道和订单类型

        $ebay_carrier   = $result['ebay_carrier'];
        $ebay_ordertype = $result['ebay_ordertype'];
        $ebay_account   = $result['ebay_account'];

        if(preg_match('/(EUB)/',$ebay_carrier)){
            if(trim($ebay_ordertype) == 'LAZADA'){
                $l_o_type = 1;
            }else{
                $l_o_type = 2;
            }
        }elseif(trim($ebay_ordertype) == 'LAZADA'){
            $l_o_type = 3;
        }

        $egersis_color = $this->getColorByTypeAndAccount($ebay_ordertype,$ebay_account);

        return array('l_o_type' => $l_o_type,'egersis_color' => $egersis_color);
    }


    /**
     * 根据平台类型和销售账号获取保护账号的警醒色
     * @param string $ordertype    平台类型
     * @param string $account      销售账号
     * @return mixed
     */
    public function getColorByTypeAndAccount($ordertype,$account){
        $nowtime = time();
        $sql = " SELECT egersis_color FROM caigouaccountstrategy
            WHERE ebay_ordertype='$ordertype' AND ebay_account='$account'
            AND status=10 AND starttime<='$nowtime' AND endtime>='$nowtime' 
            LIMIT 1";
        $color = self::$_dbconn->query($sql);
        $color = self::$_dbconn->getResultArray($color);

        if(isset($color[0]['egersis_color'])){
            $egersis_color = $color[0]['egersis_color'];
        }else{
            $egersis_color = '';
        }

        return $egersis_color;
    }


}



