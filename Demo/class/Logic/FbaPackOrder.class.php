<?php

/**
 * FBA装箱单逻辑处理
 * User: Lon
 * Date: 2018/4/17
 * Time: 17:16
 */
class FbaPackOrder{

    /**
     * 装箱单箱子的规格列表
     * @return array
     */
    public static function boxSpecificationList(){
        $list = array(
            '小箱子' => array('l' => 56,'w' => 36,'h' => 26),
            '中箱子' => array('l' => 49,'w' => 41,'h' => 43),
            '大箱子' => array('l' => 59,'w' => 50,'h' => 47),
        );
        return $list;
    }


    /**
     * 更新 装箱单的  总计抛重量、总实际重量、总计费总量
     * @param $plan_order_sn
     */
    public static function updatePackingTotalInfo($plan_order_sn){
        $packingInfoDetails = DB::Select('store_out_plan_packing_details',"plan_order_sn='$plan_order_sn'");

        $total_cast_weight = $total_actual_weight = $total_fee_weight = 0;
        foreach($packingInfoDetails as $det_val){
            $cast_weight    = $det_val['cast_weight'];
            $actual_weight  = $det_val['actual_weight'];

            $total_cast_weight      += $cast_weight;
            $total_actual_weight    += $actual_weight;
            $total_fee_weight       += ($cast_weight >= $actual_weight)?$cast_weight:$actual_weight;
        }
        $update = array(
            'cast_weight' => $total_cast_weight,
            'actual_weight' => $total_actual_weight,
            'fee_weight' => $total_fee_weight
        );
        DB::Update('store_out_plan_packing',$update,"plan_order_sn='$plan_order_sn'");

    }

    /**
     * 改变发货物流渠道时 更新装箱单的计抛值
     * @param $plan_order_sn
     * @param $old_logistics
     * @param $new_logistics
     * @return bool
     */
    public static function updatePackingCastValue($plan_order_sn,$old_logistics,$new_logistics){
        // 验证 新旧物流渠道信息是否缺失
        if(empty($old_logistics[0]) OR empty($old_logistics[1]) OR empty($new_logistics[0]) OR empty($new_logistics[1])){
            return false;
        }

        $packingInfo = DB::Select('store_out_plan_packing',"plan_order_sn='$plan_order_sn'");
        if(empty($packingInfo)) return false;// 还未装箱

        $old_car = $old_logistics[0];
        $old_log = $old_logistics[1];
        $new_car = $new_logistics[0];
        $new_log = $new_logistics[1];
        $old_cast_value = DB::Find('fba_shippingqudao',"shippingcarrierid='{$old_log}' AND ebay_carrier='{$old_car}'");
        $new_cast_value = DB::Find('fba_shippingqudao',"shippingcarrierid='{$new_log}' AND ebay_carrier='{$new_car}'");

        // 验证物流渠道的计抛值是否存在
        if(empty($old_cast_value['cast_value']) OR empty($new_cast_value['cast_value']) OR $old_cast_value['cast_value'] == $new_cast_value['cast_value']){
            return false;
        }
        $old_cast_value = $old_cast_value['cast_value'];
        $new_cast_value = $new_cast_value['cast_value'];

        // 更新 装箱明细的计抛值
        $up_packing = "UPDATE store_out_plan_packing_details 
            SET cast_weight=(cast_weight*$old_cast_value)/$new_cast_value 
            WHERE plan_order_sn='$plan_order_sn' ";
        DB::QuerySQL($up_packing);

        $up_packing = "UPDATE store_out_plan_packing SET cast_value=$new_cast_value WHERE plan_order_sn='$plan_order_sn' ";
        DB::QuerySQL($up_packing);


        self::updatePackingTotalInfo($plan_order_sn);

        return true;
    }



}