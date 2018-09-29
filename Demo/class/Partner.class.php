<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Help/DB.class.php';
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."include/tools/arrayfunction.php";

/**
 * 采购订单辅助操作类
 * Class PurchaseOrder
 */
class Partner
{
    protected static $username  = '';
    protected static $error     = '';

    public static function init(){
        if(empty(self::$username)){
            global $truename;
            self::$username = empty($truename)?'otw':$truename;
        }
    }

    /**
     * 获取指定的供应商资料
     * @param $partner 供应商名称或ID
     * @param null $status
     * @return bool|type
     */
    public static function getPartner($partner,$status = null){

        $where = " id='$partner' or company_name='$partner' ";
        if($status !== null){
//            $where .= " AND status='$status'";
        }

        $partnerInfo = DB::Find('ebay_partner',$where);
        if(empty($partnerInfo)) return false;
        return $partnerInfo;
    }

    /**
     * 修改供应商的状态
     * @param $condition
     * @param $update
     * @return type
     */
    public static function changePartnerStatus($condition,$update){
        $where = '1';
        if(isset($condition['partner_id']))     $where .= " AND id={$condition['partner_id']} ";
        if(isset($condition['partner_name']))   $where .= " AND company_name='{$condition['partner_name']}' ";

        if($where === '1') return false;

        $update_data = array();
        if(isset($update['status'])) $update_data['status'] = intval($update['status']);
        if(isset($update['isuse'])) $update_data['isuse'] = intval($update['isuse']);


        $res = DB::Update('ebay_partner',$update_data,$where);

        return $res;

    }

    /**
     * 根据供应商ID批量获取供应商资料
     * @param $ids
     * @return array|bool|int|string
     */
    public static function getPartnerNameListByIds($ids){
        $ids_str = "'".implode("','",$ids)."'";

        $partnerList = "SELECT id,company_name FROM ebay_partner WHERE id IN($ids_str) ";
        $partnerList = DB::QuerySQL($partnerList);
        $partnerList = get_array_column($partnerList,'company_name','id');

        return $partnerList;

    }

    public static function getError(){
        return self::$error;
    }


}