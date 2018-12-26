<?php
include_once dirname(dirname(__FILE__))."/include/functions.php";
include_once dirname(__FILE__)."/publicClass/LogOper.class.php";

/**
 * 刷单处理方法类
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2017/7/17
 * Time: 17:12
 */
class ShuaDanOrderDeal{

    private static $dbcon = '';

    public static function init(){
        global $dbcon;

        if(empty(self::$dbcon))  self::$dbcon = $dbcon;
    }

    public function createBuyerUserInfo(){
        self::init();

    }

    /**
     * 获得刷单订单的交运编码
     * @param $order_id 平台订单编号
     * @return bool
     */
    public static function getLogisticsCode($order_id){
        self::init();
        $sql = "SELECT * FROM purchaseorders WHERE orderid='$order_id' LIMIT 1";
        $sql = self::$dbcon->query($sql);
        $sql = self::$dbcon->getResultArray($sql);

        if($sql AND $sql[0]['logistics_code']){
            $logisticsCode = $sql[0]['logistics_code'];
        }else{
            $logisticsCode = false;
        }

        return $logisticsCode;
    }

    /**
     * 更新刷单记录的状态
     * @param $order_id  平台订单编号
     * @param $status  目的状态
     */
    public static function updateShuaDanStatus($order_id,$status){
        self::init();
        $sql = "SELECT * FROM purchaseorders WHERE orderid='$order_id' LIMIT 1";
        $sql = self::$dbcon->query($sql);
        $sql = self::$dbcon->getResultArray($sql);
        if($sql){
            // 刷单记录完成
            $sql = "UPDATE purchaseorders SET status='20' WHERE  orderid='$order_id' LIMIT 1";
            self::$dbcon->execute($sql);

            // 刷单订单出库完结
            $sql = "UPDATE ebay_order SET ebay_status='5' WHERE  recordnumber='$order_id' LIMIT 1";
            self::$dbcon->execute($sql);
        }
    }


    /**
     * 刷单拦截器 （判断指定平台、指定账号、指定SKU是否被设置刷单拦截）
     * @param int $type 匹配方式  1.根据SKU、账号、平台类型,2.根据订单编号匹配
     * @param string $skuCode  产品编码或订单编号
     * @param string $orderType  订单平台
     * @param string $account  销售账号
     * @return array|bool|string  true|false 设置拦截|未拦截
     */
    public static function judgeIsSetIntercept($type,$skuCode = '',$orderType = '',$account = ''){
        if($type == 1 AND (empty($orderType) || empty($account) || empty($skuCode))){
            return array('Error','参数不合法（订单类型、销售账号、产品编码缺一不可）');
        }elseif($type == 2 AND !is_numeric($skuCode)){
            return array('Error','参数不合法（订单编号只能是数字）');
        }elseif($type != 1 AND $type != 2 ){
            return array('Error','参数不合法（匹配类型错误）');
        }

        self::init();
        $time   = time();
        $result = false;

        if($type == 1){// 根据SKU匹配
            // 10.激活状态
            $selSql = "SELECT * FROM purchaseinterceptset 
                WHERE ebay_ordertype='$orderType' AND ebay_account='$account' AND sku='$skuCode'
                AND status=10 AND starttime<'$time' AND endtime>'$time' LIMIT 1 ";
            $selSql = self::$dbcon->query($selSql);
            $selSql = self::$dbcon->getResultArray($selSql);

            if($selSql){
                $result = true;
            }
        }elseif($type == 2){// 根据订单编号匹配
            $selSql = "SELECT count(1) 
                FROM ebay_order AS e_o
                INNER JOIN ebay_orderdetail AS e_od ON e_o.ebay_ordersn=e_od.ebay_ordersn
                INNER JOIN purchaseinterceptset AS puset 
                    ON (puset.ebay_ordertype=e_o.ebay_ordertype AND puset.ebay_account=e_o.ebay_account AND puset.sku=e_od.sku)
                WHERE e_o.ebay_id='$skuCode' AND puset.status=10 AND puset.starttime<'$time' AND puset.endtime>'$time' LIMIT 1 ";
            $selSql = self::$dbcon->query($selSql);
            $selSql = self::$dbcon->getResultArray($selSql);

            if($selSql){
                $result = true;
            }
        }

        return $result;

    }


    /**
     * 根据SKU的匹配规则转换订单类型为营销类型订单
     */
    public static function updateInfoByIntercept(){
        self::init();

        $startTime = time() ;
        $endTime = time() ;

        // 查询需要拦截的订单  market=0是未标记，标记之后不再标记
        // WISH只拦截美国的订单
        $selSql = "SELECT e_o.ebay_id,e_o.ebay_ordertype,e_o.ebay_account,e_od.ebay_itemid
                FROM ebay_order AS e_o
                INNER JOIN ebay_orderdetail AS e_od ON e_o.ebay_ordersn=e_od.ebay_ordersn
                INNER JOIN purchaseinterceptset AS puset 
                    ON (puset.ebay_ordertype=e_o.ebay_ordertype AND puset.ebay_account=e_o.ebay_account AND puset.itemid=e_od.ebay_itemid)
                WHERE puset.status=10 AND puset.starttime<'$startTime' AND puset.endtime>'$endTime'
                AND e_o.ebay_status NOT IN(500,5,400,236,2,3)  AND e_o.ebay_addtime>=puset.starttime AND market!=10
                AND (e_o.ebay_ordertype != 'WISH' OR (e_o.ebay_ordertype='WISH' AND e_o.ebay_couny='US'))
                GROUP BY e_o.ebay_id ";
//        echo $selSql;exit;
        $selSql = self::$dbcon->query($selSql);
        $selSql = self::$dbcon->getResultArray($selSql);
//        print_r($selSql);exit;

        if($selSql){
            foreach ($selSql as $value){
                $ebay_id        = $value['ebay_id'];
                $ebay_ordertype = $value['ebay_ordertype'];
                $ebay_itemid    = $value['ebay_itemid'];
                $ordertype      = self::getNewOrderType($ebay_ordertype);// 营销类型

                self::$dbcon->execute("UPDATE ebay_order SET ebay_status=500,market=10,ebay_ordertype='$ordertype' WHERE ebay_id='$ebay_id' LIMIT 1");
                LogOper::addOrderLogs($ebay_id,'恢复营销订单类型成功',89);
//                addOrderRemark($ebay_id, '订单'.$ebay_id.',['.$ebay_itemid.']恢复营销订单类型成功..');
            }
        }
    }

    /**
     * 把订单类型转换为营销的订单类型
     * @param $ebay_ordertype  订单原类型
     * @param $orderid
     * @return string
     */
    public static function getNewOrderType($ebay_ordertype,$orderid = ''){

        switch ($ebay_ordertype){//  订单类型转换
            case 'WISH':
                $newOrderType = 'Y-W';break;
            case 'ALI-EXPRESS':
                $newOrderType = 'Y-AL';break;
            case 'JOOM':
                $newOrderType = 'Y-JO';break;
            case 'EBAY订单':
                $newOrderType = 'Y-EB';break;
            case 'PP线下订单':
                $newOrderType = 'Y-PP';break;
            case 'AMAZON':
                $newOrderType = 'Y-AM';break;
            case 'LAZADA':
                $newOrderType = 'Y-LA';break;
            case 'MM订单':
                $newOrderType = 'Y-MM';break;
            case 'TOP':
                $newOrderType = 'Y-TO';break;
            case 'CD订单':
                $newOrderType = 'Y-CD';break;
            case 'SHOPEE':
                $newOrderType = 'Y-SH';break;
            case 'PM订单':
                $newOrderType = 'Y-PM';break;
            case 'NEWEGG':
                $newOrderType = 'Y-NE';break;
            case 'AMZ-FBA':
                $newOrderType = 'Y-AF';break;
            case 'WISH海外仓':
                $newOrderType = 'Y-WI';break;
            case 'PAYTM':
                $newOrderType = 'Y-PA';break;
            case 'TANGA':
                $newOrderType = 'Y-TA';break;
            case 'OpenSky':
                $newOrderType = 'Y-OP';break;
            case 'FNAC':
                $newOrderType = 'Y-FN';break;
            default:// 未能匹配到则返回原类型
                $newOrderType = $ebay_ordertype;break;

        }

        $market = self::changeOrderType($orderid);// 查询交易号是否需要特殊标记
        if(substr($newOrderType,-1) != 'T'){// 最右边一个字符等于T则表示已经标记为第三方营销，不再加后缀
            $newOrderType = $newOrderType.$market;
        }

        return $newOrderType;
    }


    /**
     * 根据交易号获取判断交易类型（自营或第三,第三方的类型后加后缀T）
     * @param string $orderid
     * @return  string $type
     */
    public static function changeOrderType($orderid){
        self::init();

        $type = '';
        if($orderid){
            $orderInfo = self::getPurchaseOrders($orderid);
            if(isset($orderInfo['type']) ){
                if($orderInfo['type'] == 2){// 批量导入为第三方信息
                    $type = 'T';// 类型后缀加一
                }
            }
        }

        return $type;
    }


    /**
     * 根据订单交易号获取交易信息
     * @param $orderid
     * @return array
     */
    public static function getPurchaseOrders($orderid){
        self::init();

        $orderInfo = "SELECT * FROM purchaseorders WHERE orderid='$orderid' LIMIT 1";
        $orderInfo = self::$dbcon->query($orderInfo);
        $orderInfo = self::$dbcon->getResultArray($orderInfo);

        return empty($orderInfo)?array():$orderInfo[0];
    }


    //恢复营销订单类型（把已经标记的交易号的订单转为其他类型）
    public static function subRepairYXOrders(){
        self::init();

        //恢复营销订单类型（查询出未完成的营销订单）
        $sql = "select orderid,tracknumber,ebay_carrier,system_shippingcarriername from purchaseorders where orderid <>'' and status <15 ";
        echo $sql . "<br>";
        $sql = self::$dbcon->execute($sql);
        $sql = self::$dbcon->getResultArray($sql);
        echo "总行数：" . count($sql) . "<br>";
        foreach ($sql as $value) {
            $orderid        = trim($value['orderid']);
            $tracknumber    = $value['tracknumber'];
            $ebay_carrier   = empty($value['ebay_carrier'])?'ShuaDan':$value['ebay_carrier'];
            $system_shippingcarriername = $value['system_shippingcarriername'];

            $orderInfo  = self::getOrderInfo($orderid);

            if($orderInfo AND $orderInfo['ebay_status'] != 500 AND $orderInfo['ebay_status'] != 5){
                $ebay_ordertype  = $orderInfo['ebay_ordertype'];
                $ebay_id         = $orderInfo['ebay_id'];
                $ebay_ordertype  = self::getNewOrderType($ebay_ordertype,$orderid);

                if($ebay_carrier == '中美专线'){
                    $sub_up = ",ebay_orderqk='$tracknumber'";
                }else{
                    $sub_up = '';
                }
                // market=10 标记已经恢复到营销订单
                $upt = "update ebay_order set ebay_tracknumber='$tracknumber',ebay_ordertype='$ebay_ordertype',ebay_status=500,market=10,
                    ebay_carrier='$ebay_carrier',system_shippingcarriername='$system_shippingcarriername' {$sub_up} 
                    WHERE recordnumber ='$orderid' limit 1 ";
                $upt = self::$dbcon->update($upt);
                if ($upt) {
                    self::$dbcon->update("UPDATE purchaseorders SET status='15' WHERE orderid='$orderid' LIMIT 1");// 交易已经标记
                    LogOper::addOrderLogs($ebay_id,'恢复营销订单类型成功',89);
//                    addOrderRemark($ebay_id, '订单'.$ebay_id.',恢复营销订单类型成功.');
//                    subOperation("repairyxorders", "恢复营销订单类型,Order ID:[" . $orderid . "]" . $upt);
                }
            }
        }

        self::updateInfoByIntercept();
    }


    /**
     * 根据交易号获取销售订单信息
     * @param $orderid
     * @return string
     */
    public static function getOrderInfo($orderid){
        self::init();

        // 排除已经标记的订单
        $ordertype = "SELECT ebay_id,ebay_ordersn,ebay_status,ebay_ordertype,recordnumber,ebay_status FROM ebay_order 
                    WHERE recordnumber ='$orderid'  LIMIT 1 ";
        $ordertype = self::$dbcon->query($ordertype);
        $ordertype = self::$dbcon->getResultArray($ordertype);

        return empty($ordertype)?array():$ordertype[0];
    }

}