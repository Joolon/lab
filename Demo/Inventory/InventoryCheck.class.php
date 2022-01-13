<?php
include_once dirname(dirname(__FILE__)).'/Help/DB.class.php';

/**
 * Class InventoryCheck
 * 库存盘点业务逻辑处理类
 * @author:zwl
 */
class InventoryCheck
{

    public static $username     = '';
    private static $table1      = 'inventory_check';// 盘点单主表
    private static $table2      = 'inventory_check_detail';// 盘点单明细表

    /**
     * 初始化 绑定内部参数
     */
    public static function init()
    {
        global  $truename;
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
    public static function createOrderSn($prefix = 'IC-',$table = 'inventory_check',$column = 'check_sn'){
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
     * 获取盘点单记录
     * @param $checkId         盘点单ID
     * @param string $checkSn  盘点单编号
     * @param bool $haveDetail 是否附带盘点单产品明细
     * @return array
     */
    public static function getInventoryCheck($checkId,$checkSn = '',$haveDetail=false){
        $where = ' 1 ';
        if($checkId) $where .= " AND id='$checkId' ";
        if($checkSn) $where .= " AND check_sn='$checkSn' ";

//        echo $where;exit;
        $checkInfo = DB::Find(self::$table1,$where);
        if($haveDetail AND $checkInfo){
            $checkSn = $checkInfo['check_sn'];
            $where  = " check_sn='$checkSn' ";
            $checkInfoDetail = DB::Select(self::$table2,$where);
            $res = array($checkInfo,$checkInfoDetail);
        }else{
            $res = array($checkInfo);
        }

//        print_r($res);exit;

        return $res;
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

