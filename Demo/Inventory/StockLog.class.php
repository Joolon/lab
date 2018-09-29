<?php

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'IOBase.class.php';

/**
 * 产品出入库日志业务操作类
 * Class StockLog
 */
class StockLog extends IOBase
{
    public static $table_name = 'io_error_log';// 错误，异常日志记录表名


    // 操作类型
    public static $eventType = array(
        11 => 'SkuIn',// SKU入库
        12 => 'SkuOut',// SKU出库
        13 => 'Onhandle',// 总库存操作

        21 => 'AddInOrder',// 添加入库单
        22 => 'AddOutOrder',// 添加出库单
        23 => 'AddInOutOrder',// 添加出入库单
        24 => 'DeleteInOutOrder',//

        31 => 'AddStorage',// 创建仓位
        32 => 'UpdateStorage',// 更新仓位

        41 => 'StockShift',// 库存转移




    );


    /**
     * 产品操作日志
     * @param string    $type       操作类型
     * @param string    $order_sn   操作类型
     * @param string    $notes      日志内容
     */
    public static function addOperationLog($type,$order_sn,$notes){
        self::init();
        // 数据类型转换
        if(is_array($notes) OR is_object($notes)){
            $notes = json_encode($notes);
        }

        $add = array();
        $add['event']       = self::$eventType[$type];
        $add['order_sn']    = $order_sn;
        $add['notes']       = $notes;
        $add['operuser']    = self::$username;
        $add['opertime']    = time();

        DB::Add(self::$table_name,$add);
    }

}
