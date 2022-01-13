<?php
include_once dirname(dirname(__FILE__)).'/Help/DB.class.php';
include_once dirname(dirname(__FILE__)).'/include/tools/arrayfunction.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Goods.class.php';

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'HhStock.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'InOutOrders.class.php';
/**
 * Class InventoryAllot
 * 调拨单业务逻辑处理类
 * @author:zwl
 */
class InventoryAllot
{

    public static $username     = '';
    private static $table1      = 'inventory_allot';
    private static $table2      = 'inventory_allot_detail';

    /**
     * 初始化 绑定内部参数
     */
    public static function init()
    {
        global $truename;
        if(!isset($truename)) $truename = empty($_SESSION['truename'])?'':$_SESSION['truename'];
        self::$username = $truename;
    }

    /**
     * 生成指定表中指定字段 唯一个编号
     * @param string $prefix  编号前缀
     * @param string $table   目标表
     * @param string $column  目标列
     * @return string 唯一编号
     */
    public static function createOrderSn($prefix = 'IA-',$table = 'inventory_allot',$column = 'allot_sn'){
        $flag = false;
        $orderSn = '';
        while(!$flag){
            $orderSn = $prefix.date('YmdHis').'-'. mt_rand(100, 999);
            $exists = DB::Find($table," $column='$orderSn' ");
            if(!$exists){
                $flag = true;
            }
        }
        return $orderSn;
    }

    /**
     * 查询调拨单信息
     * @param string $allot_sn    调拨单编号或调拨单ID
     * @param bool $have_detail   是否附加调拨单明细  true,附加 false 不附加
     * @param string $type        查询字段类型 allot_id | allot_sn  调拨单ID|调拨单编号
     * @return array              调拨单信息或错误提示
     */
    public static function getInventoryAllot($allot_sn,$have_detail = false,$type = 'allot_sn'){
        if($type == 'allot_sn'){
            $where = " allot_sn='$allot_sn' ";
        }elseif($type == 'allot_id'){
            $where = " id='$allot_sn' ";
        }else{
            return array('code' => '0X0001','msg' => '查询类型错误(请根据调拨单ID或编号查询)');
        }

        $allotInfo = DB::Find(self::$table1,$where);
        if($have_detail){
            if($type == 'allot_id') $allot_sn = $allotInfo['allot_sn'];
            $detail = DB::Select(self::$table2," allot_sn='$allot_sn' ORDER BY goods_location ASC ");
            $allotInfo['detail'] = $detail;
        }
//        print_r($allotInfo);exit;
        return $allotInfo;
    }


    /**
     * 获取调拨单的状态信息（单据状态和审核状态）
     * @param array $allot_ids  调拨单ID数组
     * @return mixed
     */
    public static function getStatusByAllotId($allot_ids){
        $allot_ids_str = implode(",",$allot_ids);
        $statusArr = DB::Select(self::$table1," id IN($allot_ids_str) ",'id,allot_sn,status,aduit_status');

        return $statusArr;
    }


    /**
     * 调拨单出库（多个ID则批量出库）
     * @param $allot_ids
     * @return array
     */
    public static function outStoreByAllotId($allot_ids){
        self::init();
        $return     = array();
        $date       = date('Y-m-d H:i:s');

        // 判断调拨单是否处于新建或打印状态，且单据已审核
        $statusArr = self::getStatusByAllotId($allot_ids);
        $flag = true;
        foreach($statusArr as $status){
            if(($status['status'] != 10 AND $status['status'] != 20) OR $status['aduit_status'] != 10){
                $flag = false;
                $return[ $status['id'] ] = array('code' => '0X0001','msg' => '调拨单非新建或打印状态，或单据未审核');
            }
        }

        if($flag === false) return $return;


        if($allot_ids){
            foreach($allot_ids as $allot_id ){
                $allotInfo = self::getInventoryAllot($allot_id,true,'allot_id');// 获取调拨单信息
//                print_r($allotInfo);exit;

                $allot_sn = $allotInfo['allot_sn'];
                $res = self::doOutStore($allotInfo);// 出库：更新库存
//                print_r($allotInfo);exit;
                // 创建出库单记录
                $inOrder = array();
                $inOrder['order_type']  = '49';// 调拨出库
                $inOrder['order_sn']    = $allotInfo['allot_sn'].'-OUT';;
                $inOrder['store_id']    = $allotInfo['begin_store_id'];
                $inOrder['io_time']     = date('Y-m-d H:i:s');
                $inOrder['operator']    = self::$username;

                foreach($allotInfo['detail'] as $value){
                    if($value['real_out_qty'] <=0) continue;// 实际未出库（没货）
                    $sku_detail = array();
                    $sku_detail['sku']          = $value['sku'];
                    $sku_detail['quantity']     = $value['real_out_qty'];// 绝对值
                    $sku_detail['storage_sn']   = $value['goods_location'];
                    $sku_detail['old_quantity'] = 0;
                    $sku_detail['goods_cost']   = empty($value['goods_cost']) ? 0 : $value['goods_cost'];
                    $sku_detail['total_cost']   = $sku_detail['quantity'] * $sku_detail['goods_cost'];

                    $inOrder['sku_details'][] = $sku_detail;
                }
                $res = InOutOrders::createOutOrder($inOrder);

//                print_r($res);exit;

//                var_dump($res);exit;
                // 更新调拨单出库状态
                if($res){
                    $update = array('status' => 30,'delivery_time' => $date );// 已出库
                    DB::Update(self::$table1,$update," allot_sn='$allot_sn' ");
                    $return[$allot_id] = array('code' => '0X0000','msg' => '调拨单出库成功');

                    self::addOperationLog($allot_sn,$update,30);
                }else{
                    $return[$allot_id] = array('code' => '0X0001','msg' => '调拨单出库失败：'.InOutOrders::getError());
                }
//                print_r($return);exit;
            }
        }else{
            $return[] = array('code' => '0X0001','msg' => '调拨单ID错误（参数错误）');
        }

        return $return;
    }

    /**
     * 更新调拨单 SKU实际出库数量
     * @param $allot_sn 调拨单编号
     * @param $update 更新SKU的数据
     * @return array
     */
    public static function updateRealAmount($allot_sn,$update){
        $allotInfo = self::getInventoryAllot($allot_sn);

        if($allotInfo['status'] > 20 ){ // 调拨单已出库（20.已打印）
            $result = array('code' => '0X0001', 'msg' => '状态不对：调拨单已出库状态');
            return $result;
        }

        // 记录序号与出库数量
        $details = explode('&',$update);
        $updateTmp = array();
        foreach($details as $value){
            list($detail_id,$out_qty) = explode('=',$value);
            $updateTmp['real_out_qty'] = (int)$out_qty;
            DB::Update(self::$table2,$updateTmp," id='$detail_id' ");
            self::addOperationLog($allot_sn,$updateTmp,15);
        }

        $result = array('code' => '0X0000','msg' => '调拨单出库更新成功');
        return $result;
    }

    /**
     * 调拨单出库 扣库存
     * @param $allotInfo
     * @return bool
     */
    public static function doOutStore($allotInfo){
        return true;
        $warehouseId =  $allotInfo['begin_store_id'];// 起始仓库
        if($allotInfo['detail']){
            foreach($allotInfo['detail'] as $detail){
                $outSku     = $detail['sku'];
                $outLocation = $detail['goods_location'];
                $outAmount  = $detail['out_qty'];
                $realOutAmount  = $detail['real_out_qty'];
                if($realOutAmount <=0) continue;// 实际未出库（没货）

            }
        }

        return true;
    }


    /**
     * 调拨单入库（质检入库操作界面）
     * @param array $putInfo  输入的入库数量
     * @return array
     */
    public static function putInStore($putInfo){
        self::init();

        $allot_sn = $putInfo['allot_sn'];
        $allotInfo = self::getInventoryAllot($allot_sn);
        $end_store_id = $allotInfo['end_store_id'];

        $details = $putInfo['detail'];// 入库数量明细
//        print_r($details);exit;

        //  检验质检数量和实际调拨数量是否相符

        $detailsTmp = array();
        foreach($details as $value){
            $inSku          = $value['sku'];
            $inOutQty       = $value['out_qty'];
            $inGoodQty      = $value['good_sku'];
            $inStorageSn    = $value['storage_sn'];
            $inGoodQtyBk      = $value['good_sku_bk'];
            $inStorageSnBk    = $value['storage_sn_bk'];

            if(empty($inStorageSn)){// 验证入库仓位
                $return = array('code' => '0X0001','msg' => $inSku.'：入库仓位为空');
                return $return;
            }
            unset($value['good_sku_bk'],$value['storage_sn_bk']);
            $detailsTmp[] = $value;
            if($inGoodQty AND $inStorageSnBk){// 含有备选入库仓位
                $value['good_sku']      = $inGoodQtyBk;
                $value['storage_sn']    = $inStorageSnBk;
                $detailsTmp[] = $value;
            }

            if( ($inStorageSnBk AND $inOutQty != (int)$inGoodQty + (int)$inGoodQtyBk )
                    OR  (empty($inStorageSnBk) AND $inOutQty != (int)$inGoodQty)){
                $return = array('code' => '0X0001','msg' => $inSku.'：质检数量不等于调拨出库数量之和');
                return $return;
            }

        }
        $details = $detailsTmp;
        unset($detailsTmp);
//        print_r($details);exit;

        // 更新库存
        foreach($details as $value){
            $inSku      = $value['sku'];
            $inGoodQty   = (int)$value['good_sku'];// 良品数+不良品数
            $inStorageSn = $value['storage_sn'];

            // 更新调拨入库 库位记录
           DB::QuerySQL("UPDATE ".self::$table2." SET good_qty=good_qty+$inGoodQty,in_location=concat(IFNULL(in_location,''),'$inStorageSn,' )
                        WHERE sku='$inSku' AND allot_sn='$allot_sn' LIMIT 1 ");

        }
//        var_dump($res);exit;

        // 创建入库单
        $inOrder = array();
        $inOrder['order_type']  = '50';// 调拨入库
        $inOrder['order_sn']    = $allotInfo['allot_sn'].'-IN';;
        $inOrder['store_id']    = $allotInfo['end_store_id'];
        $inOrder['io_time']     = date('Y-m-d H:i:s');
        $inOrder['operator']    = self::$username;

        foreach($details as $value){
            $sku_detail = array();
            $sku_detail['sku']          = $value['sku'];
            $sku_detail['quantity']     = (int)$value['good_sku'] + (int)$value['bad_sku'];
            $sku_detail['storage_sn']   = $value['storage_sn'];
            $sku_detail['goods_cost']   = empty($value['goods_cost']) ? 0 : $value['goods_cost'];
            $sku_detail['total_cost']   = $sku_detail['quantity'] * $sku_detail['goods_cost'];

            $inOrder['sku_details'][] = $sku_detail;
        }
//        print_r($inOrder);exit;
        $res = InOutOrders::createInOrder($inOrder);
//        var_dump($res);exit;


        $update = array('status' => 100,'putin_time' => date('Y-m-d H:i:s') );
        DB::Update(self::$table1,$update," allot_sn='$allot_sn' ");
        self::addOperationLog($allot_sn,$update,40);

        $return = array('code' => '0X0000','msg' => '质检入库成功');
        return $return;
    }


    /**
     * 添加调拨单操作日志
     * @param string $allot_sn 调拨单编号
     * @param array $updateArr 更新字段数组
     * @param int $type 更新类型
     */
    public static function addOperationLog($allot_sn,$updateArr,$type){
        self::init();

        $add = array(
            'allot_sn' => $allot_sn,
            'operuser' => self::$username,
            'opertime' => date('Y-m-d H:i:s'),
            'notes' => json_encode($updateArr),
            'types' => self::$operLogCode[$type]
        );
        DB::Add('inventory_allotlog',$add);
    }


    /**
     * 获取调拨单中 SKU重复的记录
     * @return array
     */
    public static function repeatAllotList(){
        // 统计未出库的调拨单中 SKU重复的记录
        $sqlSkuRepeat = "SELECT allot_sn,sku FROM inventory_allot_detail WHERE sku IN(
                  SELECT i_a_d.sku
                  FROM inventory_allot AS i_a 
                  LEFT JOIN inventory_allot_detail AS i_a_d ON i_a_d.allot_sn=i_a.allot_sn
                  WHERE i_a.status NOT IN(100,-10) GROUP BY i_a_d.sku HAVING count(1) >1 
                )";
        $skuRepeat = DB::QuerySQL($sqlSkuRepeat);
        $repeat_allot_sn_arr = get_array_column($skuRepeat,'allot_sn','allot_sn');
        $repeat_sku_arr = get_array_column($skuRepeat,'sku','sku');

        $result = array('allot_list' => $repeat_allot_sn_arr,'sku_list' => $repeat_sku_arr);
        return $result;
    }

    // 更新类型状态码
    private static $operLogCode = array(
        10 => 'UpdateStatus',// 更新单据状态
        15 => 'UpdateSku',// 更新单据状态
        20 => 'UpdateCheckStatus',// 更新单据审核状态
        30 => 'OutStore',// 调拨单出库
        40 => 'PutIn',// 调拨入库
        50 => 'PrintIA',// 打印调拨单
    );


}

