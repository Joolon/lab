<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Help/DB.class.php';

/**
 * 采购订单辅助操作类
 * Class PurchaseOrder
 */
class PurchaseOrder
{
    protected static $username  = '';
    protected static $error     = '';

    protected static function init(){
        if(empty(self::$username)){
            global $truename;
            self::$username = empty($truename)?'otw':$truename;
        }
    }

    /**
     * 创建一个唯一的采购单号（自动验证单号是否已经存在）
     * @return string
     */
    public static function createUniqueOrderSn(){

        do{
            $io_ordersn = "IO-".date('Y')."-".date('m')."-".date('d')."-".date("H").date('i').date('s')."-".mt_rand(1000, 9999);
            $puOrderInfo = self::getPurchaseOrder(array('io_ordersn' => $io_ordersn));
            if(empty($puOrderInfo)){
                break;
            }
        }while(true);

        return $io_ordersn;
    }

    /**
     * 查找一个采购单记录
     * @param $condition
     *      $condition = array(
     *          'id' => '采购单ID',
     *          'io_ordersn' => '采购单编号',
     *          'io_warehouse' => '采购单仓库',
     * )
     * @param bool $detail 附带采购单明细 true|false 是|否
     * @return type
     */
    public static function getPurchaseOrder($condition,$detail = false){
        $where = ' type=2 ';
        if(isset($condition['id']))
            $where .= " AND id='{$condition['id']}'";
        if(isset($condition['io_ordersn']))
            $where .= " AND io_ordersn='{$condition['io_ordersn']}'";
        if(isset($condition['io_warehouse']))
            $where .= " AND io_warehouse='{$condition['io_warehouse']}'";
        if(isset($condition['ebay_user']))
            $where .= " AND ebay_user='{$condition['ebay_user']}' ";

        $orderInfo = DB::Find('ebay_iostore',$where);
        if($orderInfo AND $detail === true){
            unset($condition);
            $condition['io_ordersn'] = $orderInfo['io_ordersn'];
            $orderInfo['sku_details'] = self::getPurchaseOrderDetail($condition);
        }
        return $orderInfo;
    }

    /**
     * 获取采购单明细
     * @param $condition
     * *      $condition = array(
     *          'id' => '采购单明细ID',
     *          'io_ordersn' => '采购单编号',
     *          'goods_id' => '产品ID',
     *          'goods_sn' => '产品编码',
     * )
     * @return array|bool|type
     */
    public static function getPurchaseOrderDetail($condition){
        $where = '1';
        if(isset($condition['id']))
            $where .= " AND id='{$condition['id']}'";
        if(isset($condition['io_ordersn']))
            $where .= " AND io_ordersn='{$condition['io_ordersn']}'";
        if(isset($condition['goods_id']))
            $where .= " AND goods_id='{$condition['goods_id']}'";
        if(isset($condition['goods_sn']))
            $where .= " AND goods_sn='{$condition['goods_sn']}'";

        if($where != '1'){
            $detail = DB::Select('ebay_iostoredetail',$where);
            if(empty($detail)) return array();
            return $detail;
        }else{
            self::$error = '查询条件缺失';
            return false;
        }

    }

    /**
     * 获取采购合同
     * @param $io_ordersn
     * @return type
     */
    public static function getPurchaseOrderContract($io_ordersn){
        $result = DB::Find('ebay_iostore_contract',"io_ordersn='$io_ordersn'");
        return $result;

    }

    /**
     * 检查整单是否已全部生成入库单
     * @param $order_sn 采购单号
     * @return bool
     */
    public static function checkPurchaseOrderIsComplete($order_sn){
        if(empty($order_sn)){
            self::$error = '采购单号缺失';
            return false;
        }

        $sql = "select * from ebay_iostoredetail 
            where (status='' or (status='FPRK' and goods_count>goods_count0)) and io_ordersn='$order_sn'";
        $res = DB::QuerySQL($sql);
        if(empty($res)){// 完全生成入库单
            // 更新采购单完全入库（  2.完全入库  4.生成入库单）
            $sqlUp = "update ebay_iostore set io_status = '2',stockstatus=4 where io_ordersn='$order_sn'";
            $res = DB::QuerySQL($sqlUp);

            return true;
        }else{
            return false;
        }
    }

    /**
     * 判断采购单是否可以自动审核
     * @param $io_ordersn
     * @return bool
     */
    public static function ableAutoCheckOrder($io_ordersn){
        include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'class/Goods.class.php';

        $puOrderInfo = self::getPurchaseOrder(array('io_ordersn' => $io_ordersn),true);

        if(intval($puOrderInfo['io_paidtotal']) < 200){// 总金额小于200自动审核
            return true;
        }

        if(intval($puOrderInfo['io_paidtotal']) >= 3000){// 总金额大于3000 需要财务审核
            return false;
        }


        $flags = array();
        $details = $puOrderInfo['sku_details'];
        foreach($details as $sku_val){
            $flag = false;// 假设不能自动审核

            $goods_sn       = $sku_val['goods_sn'];
            $goods_count    = $sku_val['goods_count'];                                                                  // 采购数量
            $cgjh_qty       = empty($sku_val['cgjh_qty'])?$goods_count:$sku_val['cgjh_qty'];                           // 采购计划数
            $goods_cost     = $sku_val['goods_cost'];                                                                   // 采购价

            $oldPurPrice = Goods::getGoodsInfo($goods_sn);
            $oldPurPrice = $oldPurPrice['goods_cost'];// 历史采购价


            // 满足任一条件则表示 可以自动审核
            if($goods_cost < 50 AND $goods_count / $cgjh_qty < 1.1){                                                    // 采购价格<50 和 订货数量/计划数量<110%-----不审核
                $flag = true;
            }
            if($goods_cost > 50 AND $goods_count <= $cgjh_qty ){                                                         // 采购价格>50元 和 订货数量<计划数量------不审核
                $flag = true;
            }
            if( ($goods_cost - $oldPurPrice < 0.5) AND ($goods_cost / $oldPurPrice < 1.1) ){                            // 采购价格-历史价格<0.5元 和 采购价格/历史价格<110%  不审核
                $flag = true;
            }
            $flags[] = $flag;
        }

        if(in_array(false,$flags)){
            return false;
        }else{
            return true;
        }

    }

    /**
     * 判断采购单是否需要财务审核
     * @param $io_ordersn
     * @return bool
     */
    public static function needFinancingCheckOrder($io_ordersn){
        $puOrderInfo = self::getPurchaseOrder(array('io_ordersn' => $io_ordersn),false);

//        var_dump($puOrderInfo['io_paidtotal']);exit;
        if(intval($puOrderInfo['io_paidtotal']) >= 3000){// 总金额大于3000 需要财务审核
            return true;
        }

        return false;
    }

    /**
     * 查询指定SKU的历史采购价
     * @param $sku  SKU编码
     * @param bool|int $limit  查询结果条数（默认10条,设为false则查询所有）
     * @return bool|int
     */
    public static function getOldPurchasePrice($sku,$limit = 10){
        $sql = "SELECT a.io_ordersn,b.goods_sn,b.goods_cost FROM ebay_iostore AS a 
            LEFT JOIN ebay_iostoredetail b ON (a.io_ordersn=b.io_ordersn) 
            WHERE ebay_user='otw' AND goods_sn ='$sku' AND goods_cost != '' AND io_status IN(1,2,3,4)
            AND `type` = 2 ORDER BY a.id DESC";

        if($limit !== false){
            $sql .= "  LIMIT $limit ";
        }
        $list = DB::QuerySQL($sql);
        return $list;
    }

    /**
     * 更新采购单明细为分批入库
     * @param array $condition
     * @param int $in_quantity 入库数量
     * @return bool
     */
    public static function updatePurchaseOrderDetailFPRK($condition,$in_quantity){
        $where = '1';
        if(isset($condition['id']))
            $where .= " AND id='{$condition['id']}'";
        if(isset($condition['io_ordersn']))
            $where .= " AND io_ordersn='{$condition['io_ordersn']}'";
        if(isset($condition['goods_id']))
            $where .= " AND goods_id='{$condition['goods_id']}'";
        if(isset($condition['goods_sn']))
            $where .= " AND goods_sn='{$condition['goods_sn']}'";

        if($where == '1' OR (empty($condition['id']) AND empty($condition['io_ordersn']) )){
            self::$error = '查询条件缺失';
            return false;
        }

        $update = array();
        $puOrderDetail  = self::getPurchaseOrderDetail($condition);
        $puOrderInfo    = self::getPurchaseOrder(array('io_ordersn' => $puOrderDetail[0]['io_ordersn']));

        // 更新入库数量
        $update['goods_count0'] = $puOrderDetail[0]['goods_count0'] + intval($in_quantity);
        $update['goods_count1'] = $puOrderDetail[0]['goods_count1'] + intval($in_quantity);

        $update['status'] = 'FPRK';// 设置为分批入库
        $update['audit0'] = $update['audit1'] = $update['audit2'] = self::$username;

        $res = DB::Update('ebay_iostoredetail',$update,$where);//修改子记录状态

        self::updateGoodsCost($puOrderDetail[0]['io_ordersn'],$puOrderInfo['io_warehouse'],$puOrderDetail[0]['goods_sn'],$in_quantity);

        if(!$res){
            self::$error = '数据插入数据库失败';
            return false;
        }
        return true;
    }

    /**
     * 更新产品的成本
     * @param $io_ordersn
     * @param $store_id
     * @param $goods_sn
     * @param $amount
     */
    public static function updateGoodsCost($io_ordersn,$store_id,$goods_sn,$amount){

        $puOrderDetail  = self::getPurchaseOrderDetail(array('io_ordersn' => $io_ordersn,'goods_sn' => $goods_sn));
        $goods_cost     = $puOrderDetail[0]['goods_cost'];// 采购单的采购价

        $lastcaigouprice = $goods_cost;
        $goodsInfo      = Goods::getGoodsInfo($goods_sn);
        $oldgoodscost   = $goodsInfo['goods_cost'];// 库存成本
        $oldonhandle    = HhStock::getTotalStock($store_id,$goods_sn);// 库存数

        if($goods_cost != $oldgoodscost AND $goods_cost>0 AND $oldgoodscost>0 AND $oldonhandle>0 AND $amount >0){	//采购价和库存价不相同时记录日志
            //加权平均法计算成本价 = (库存单价 * 库存数 + 采购单价*实际到货数量)/(库存数 + 实际到货数量)
            $goods_cost = ($oldgoodscost * $oldonhandle + $goods_cost * $amount) / ($oldonhandle + $amount);
            $goods_cost = number_format($goods_cost,2);
        }

        if($goods_cost != 0){
            $update_sql	= "update ebay_goods set goods_cost=$goods_cost where goods_sn='$goods_sn' ";
            DB::QuerySQL($update_sql);
        }
    }


    public static function getError(){
        return self::$error;
    }


}