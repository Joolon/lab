<?php
include_once dirname(dirname(__FILE__)) . '/Help/DB.class.php';
$aa = new order_importApi();
$id=$_REQUEST['id'];
$aa->order_import($id);
/**
 * Created by PhpStorm.
 * User: tl
 * Date: 2017/10/17
 * Time: 9:12
 */
class order_importApi{
    /**
     * 根据订单号导入订单到系统
     * @param $recordnumber
     */
    function order_import($recordnumber){
        $importsql = DB::Find("system_order_import","recordnumber='".$recordnumber."'");
        if($this->judgeOrderExists($recordnumber,$importsql['ebay_account'])){//判断订单是否存在若存在则跳过
            $result=DB::Update('system_order_import',array('status'=>30),'id='.$importsql['id']);
            return;
        }
        $detailimportsql = DB::Select("system_orderdetail_import","recordnumber='".$recordnumber."'");
        foreach ($detailimportsql as $key => $value){
            $detail_array=array(
                'ebay_ordersn'=>$value['ebay_ordersn'],
                'ebay_itemtitle'=>$value['ebay_itemtitle'],
                'ebay_amount'=>$value['ebay_amount'],
                'ebay_user'=>$value['ebay_user'],
                'sku'=>$value['sku'],
                'ebay_account'=>$value['ebay_account'],
                'addtime'=>time(),
                'recordnumber'=>$value['recordnumber'],
                'storeid'=>$value['storeid'],
                'notes'=>$value['notes'],
                'ebay_itemprice'=>$value['ebay_itemprice']
            );
            $detail=DB::Add("ebay_orderdetail", $detail_array);
            if ($detail !== 0){
                echo '--系统订单详情表添加成功--';
            }else{
                echo '--系统订单详情表添加失败--';
            }
        }
        $order_array = array(
            'ebay_ordersn'=>$importsql['ebay_ordersn'],
            'ebay_orderqk'=>$importsql['ebay_orderqk'],
            'ebay_paystatus'=>$importsql['ebay_paystatus'],
            'recordnumber'=>$importsql['recordnumber'],
            'ebay_tid'=>$importsql['ebay_tid'],
            'ebay_ptid'=>$importsql['ebay_ptid'],
            'ebay_orderid'=>$importsql['ebay_orderid'],
            'ebay_createdtime'=>time(),
            'ebay_paidtime'=>$importsql['ebay_paidtime'],
            'ebay_userid'=>$importsql['ebay_userid'],
            'ebay_username'=>$importsql['ebay_username'],
            'ebay_usermail'=>$importsql['ebay_usermail'],
            'ebay_street'=>$importsql['ebay_street'],
            'ebay_street1'=>$importsql['ebay_street1'],
            'ebay_city'=>$importsql['ebay_city'],
            'ebay_state'=>$importsql['ebay_state'],
            'ebay_countryname'=>$importsql['ebay_countryname'],
            'ebay_postcode'=>$importsql['ebay_postcode'],
            'ebay_phone'=>$importsql['ebay_phone'],
            'ebay_total'=>$importsql['ebay_total'],
            'ebay_status'=>$importsql['ebay_status'],
            'ebay_user'=>$importsql['ebay_user'],
            'ebay_addtime'=>time(),
            'ebay_shipfee'=>$importsql['ebay_shipfee'],
            'ebay_account'=>$importsql['ebay_account'],
            'ebay_note'=>$importsql['ebay_note'],
            'ebay_carrier'=>$importsql['ebay_carrier'],
            'ebay_warehouse'=>$importsql['ebay_warehouse'],
            'ebay_currency'=>$importsql['ebay_currency'],
            'ebay_phone1'=>$importsql['ebay_phone1'],
            'ebay_ordertype'=>$importsql['ebay_ordertype'],
            'eBayPaymentStatus'=>$importsql['eBayPaymentStatus'],
        );
        $order_result=DB::Add("ebay_order", $order_array);
        if ($order_result != 0){
            $result=DB::Update('system_order_import',array('status'=>20),'id='.$importsql['id']);
            $result = '订单导入成功';
            return $result;
        }else{
            $result = '订单导入失败';
            return $result;
        }
    }

    /**
     * 判断订单是否存在
     * function judgeOrderExists
     * @param $order_id
     * @param $ebay_account
     * @return bool
     */
    function judgeOrderExists($order_id,$ebay_account){
        $sql	= "select ebay_id from ebay_order where recordnumber='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $res	= DB::QuerySQL($sql);
        if(count($res) >= 1){
            return true;
        }
        $sql	="select ebay_id from ebay_order_HistoryRcd where recordnumber='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $res	= DB::QuerySQL($sql);
        if(count($res) >= 1){
            return true;
        }
        return false;
    }
}
?>